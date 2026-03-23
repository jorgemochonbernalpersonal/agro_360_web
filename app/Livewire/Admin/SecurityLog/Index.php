<?php

namespace App\Livewire\Admin\SecurityLog;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $filterDate  = '';
    public $filterLevel = '';
    public $filterEvent = '';
    public $search      = '';

    protected $queryString = ['filterDate', 'filterLevel', 'filterEvent', 'search'];

    public function mount()
    {
        $this->filterDate = now()->format('Y-m-d');
    }

    public function updatingSearch()      { $this->resetPage(); }
    public function updatingFilterDate()  { $this->resetPage(); }
    public function updatingFilterLevel() { $this->resetPage(); }
    public function updatingFilterEvent() { $this->resetPage(); }

    // ─── Log files ────────────────────────────────────────────────────────────

    private function getLogFiles(): array
    {
        $files = glob(storage_path('logs/security-*.log')) ?: [];

        $dates = [];
        foreach ($files as $file) {
            if (preg_match('/security-(\d{4}-\d{2}-\d{2})\.log$/', $file, $m)) {
                $dates[$m[1]] = $file;
            }
        }

        krsort($dates);
        return $dates;
    }

    private function parseLogFile(string $filePath): Collection
    {
        if (!file_exists($filePath)) {
            return collect();
        }

        $entries  = collect();
        $lines    = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];

        foreach ($lines as $line) {
            // [2024-03-15 10:30:00] local.warning: Message {"key":"val"} []
            if (preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] \w+\.(\w+): (.+?) (\{.*\})\s*(?:\[\])?\s*$/', $line, $m)) {
                $context = json_decode($m[4], true) ?? [];
                $entries->push([
                    'timestamp' => $m[1],
                    'level'     => strtolower($m[2]),
                    'message'   => trim($m[3]),
                    'event'     => $context['event'] ?? '',
                    'ip'        => $context['ip'] ?? '',
                    'email'     => $context['email'] ?? ($context['user_email'] ?? ''),
                    'user_id'   => $context['user_id'] ?? null,
                    'admin_id'  => $context['admin_id'] ?? null,
                    'context'   => $context,
                ]);
            }
        }

        return $entries->reverse()->values(); // newest first
    }

    private function getEntries(): Collection
    {
        $logFiles = $this->getLogFiles();
        $file     = $logFiles[$this->filterDate] ?? null;

        if (!$file) {
            return collect();
        }

        return $this->parseLogFile($file);
    }

    // ─── Render ───────────────────────────────────────────────────────────────

    public function render()
    {
        $logFiles = $this->getLogFiles();

        // Auto-select most recent file
        if ($this->filterDate && !isset($logFiles[$this->filterDate])) {
            $this->filterDate = array_key_first($logFiles) ?? now()->format('Y-m-d');
        }

        $entries = $this->getEntries();

        // Apply filters
        if ($this->filterLevel) {
            $entries = $entries->filter(fn($e) => $e['level'] === $this->filterLevel);
        }

        if ($this->filterEvent) {
            $entries = $entries->filter(fn($e) => str_contains($e['event'], $this->filterEvent));
        }

        if ($this->search) {
            $s = strtolower($this->search);
            $entries = $entries->filter(function ($e) use ($s) {
                return str_contains(strtolower($e['ip'] ?? ''), $s)
                    || str_contains(strtolower($e['email'] ?? ''), $s)
                    || str_contains(strtolower($e['message'] ?? ''), $s)
                    || str_contains(strtolower($e['event'] ?? ''), $s);
            });
        }

        $entries = $entries->values();

        $perPage     = 50;
        $currentPage = $this->getPage();
        $paginated   = new LengthAwarePaginator(
            $entries->forPage($currentPage, $perPage),
            $entries->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        $allEntries = $this->filterLevel || $this->filterEvent || $this->search
            ? $entries
            : $this->getEntries();

        $stats = [
            'total'    => $entries->count(),
            'warnings' => $entries->whereIn('level', ['warning', 'notice'])->count(),
            'alerts'   => $entries->whereIn('level', ['alert', 'critical', 'emergency', 'error'])->count(),
            'info'     => $entries->where('level', 'info')->count(),
        ];

        return view('livewire.admin.security-log.index', [
            'entries'  => $paginated,
            'logDates' => array_keys($logFiles),
            'stats'    => $stats,
        ])->layout('layouts.app', [
            'title'       => 'Log de Seguridad - Admin - Agro365',
            'description' => 'Historial de eventos de seguridad del sistema',
        ]);
    }
}
