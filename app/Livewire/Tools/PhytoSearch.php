<?php

namespace App\Livewire\Tools;

use App\Models\AppSetting;
use App\Models\PhytoRegistryProduct;
use App\Models\PhytoRegistryUse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Buscador público del registro oficial de fitosanitarios: usos autorizados
 * por cultivo y plaga, con dosis y plazo de seguridad.
 */
class PhytoSearch extends Component
{
    use WithPagination;

    /** "Vid" genérica: por defecto, que es nuestro público. */
    public const DEFAULT_CROP = '0101020700000000';

    #[Url(as: 'cultivo', except: self::DEFAULT_CROP)]
    public string $crop = self::DEFAULT_CROP;

    #[Url(as: 'plaga', except: '')]
    public string $agent = '';

    #[Url(as: 'q', except: '')]
    public string $query = '';

    public function updatedCrop(): void
    {
        $this->agent = '';
        $this->resetPage();
    }

    public function updatedAgent(): void
    {
        $this->resetPage();
    }

    public function updatedQuery(): void
    {
        $this->resetPage();
    }

    /**
     * Cultivos con algún uso vigente, agrupados: primero la vid.
     *
     * @return array{vine: array<string, string>, other: array<string, string>}
     */
    #[Computed]
    public function cropOptions(): array
    {
        $options = Cache::remember('phyto-search:crops:'.$this->sourceDate(), 86400, fn () => PhytoRegistryUse::query()
            ->whereHas('product', fn (Builder $q) => $q->where('status', PhytoRegistryProduct::STATUS_ACTIVE))
            ->select('crop_code', 'crop')
            ->distinct()
            ->orderBy('crop')
            ->pluck('crop', 'crop_code')
            ->all());

        $vine = array_filter($options, fn (string $code) => str_starts_with($code, PhytoRegistryUse::VINE_CROP_CODE_PREFIX), ARRAY_FILTER_USE_KEY);

        return ['vine' => $vine, 'other' => array_diff_key($options, $vine)];
    }

    /** @return Collection<int, string> */
    #[Computed]
    public function agentOptions(): Collection
    {
        return $this->baseQuery(withAgent: false, withText: false)
            ->distinct()
            ->orderBy('agent')
            ->pluck('agent');
    }

    #[Computed]
    public function sourceDate(): ?string
    {
        return AppSetting::get(PhytoRegistryProduct::SOURCE_DATE_SETTING);
    }

    public function render()
    {
        $uses = $this->baseQuery()
            ->with('product')
            ->join('phyto_registry_products as p', 'p.id', '=', 'phyto_registry_uses.product_id')
            ->orderBy('phyto_registry_uses.agent')
            ->orderBy('p.name')
            ->select('phyto_registry_uses.*')
            ->paginate(25);

        return view('livewire.tools.phyto-search', ['uses' => $uses]);
    }

    /**
     * Un uso autorizado en un cultivo "padre" vale para sus hijos: "Vid" cubre
     * "Vid de vinificación". Los códigos son jerárquicos de 2 en 2 dígitos,
     * rellenos con ceros: 0101020702000000 → 0101020700000000 → 0101020000000000…
     *
     * @return array<int, string>
     */
    public static function cropWithAncestors(string $code): array
    {
        $code = preg_replace('/\D/', '', $code) ?? '';
        $length = strlen($code);
        $codes = [$code];

        // Longitud significativa redondeada a par ("…10" termina en cero pero es un nivel).
        $significant = strlen(rtrim($code, '0'));
        $significant += $significant % 2;

        for ($i = $significant - 2; $i >= 2; $i -= 2) {
            $codes[] = str_pad(substr($code, 0, $i), $length, '0');
        }

        return array_values(array_unique($codes));
    }

    /** "0101020700000000" → "01010207": lo que comparten todos sus cultivos hijos. */
    public static function cropPrefix(string $code): string
    {
        $code = preg_replace('/\D/', '', $code) ?? '';
        $significant = strlen(rtrim($code, '0'));

        return substr($code, 0, max(2, $significant + $significant % 2));
    }

    /** @return Builder<PhytoRegistryUse> */
    private function baseQuery(bool $withAgent = true, bool $withText = true): Builder
    {
        // Grupos que engloban al cultivo ("Leñosas") + variedades que cuelgan de él
        // ("Vid de vinificación" al elegir "Vid"). Cada fila indica su cultivo exacto.
        $query = PhytoRegistryUse::query()
            ->whereHas('product', fn (Builder $q) => $q->where('status', PhytoRegistryProduct::STATUS_ACTIVE))
            ->where(fn (Builder $q) => $q
                ->whereIn('phyto_registry_uses.crop_code', self::cropWithAncestors($this->crop))
                ->orWhere('phyto_registry_uses.crop_code', 'like', self::cropPrefix($this->crop).'%'));

        if ($withAgent && $this->agent !== '') {
            $query->where('phyto_registry_uses.agent', $this->agent);
        }

        $text = trim($this->query);
        if ($withText && mb_strlen($text) >= 2) {
            $query->whereHas('product', fn (Builder $q) => $q
                ->where('name', 'like', "%{$text}%")
                ->orWhere('registration_number', $text)
                ->orWhere('active_substances', 'like', "%{$text}%"));
        }

        return $query;
    }
}
