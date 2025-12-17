<?php

namespace App\Livewire\Viticulturist\Personal\Viticulturist;

use App\Models\User;
use App\Models\WineryViticulturist;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class Create extends Component
{
    public $name = '';
    public $email = '';
    public $winery_id = ''; // Opcional

    public function mount()
    {
        $user = Auth::user();
        $wineries = $user->wineries;
        
        if ($wineries->count() === 1) {
            $this->winery_id = $wineries->first()->id;
        }
    }

    protected function rules(): array
    {
        $user = Auth::user();
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'winery_id' => [
                'nullable',
                'exists:users,id',
                function ($attribute, $value, $fail) use ($user) {
                    if ($value) {
                        $exists = WineryViticulturist::where('viticulturist_id', $user->id)
                            ->where('winery_id', $value)
                            ->exists();
                        if (!$exists) {
                            $fail('No estás asignado a esta bodega.');
                        }
                    }
                },
            ],
        ];
    }

    public function save()
    {
        $this->validate();
        
        $creator = Auth::user();
        $wineries = $creator->wineries;
        
        // Validar winery si se proporciona
        if ($this->winery_id && !$wineries->contains('id', $this->winery_id)) {
            $this->addError('winery_id', 'No estás asignado a esta bodega.');
            return;
        }
        
        // Generar password aleatoria
        $password = Str::password(12);
        $viticulturistId = null;
        
        try {
            DB::transaction(function () use ($creator, $password, &$viticulturistId) {
                // Crear usuario (NO verificar email todavía, se verificará cuando cambie la contraseña)
                $viticulturist = User::create([
                    'name' => $this->name,
                    'email' => $this->email,
                    'password' => Hash::make($password),
                    'role' => 'viticulturist',
                    'email_verified_at' => null, // NO verificar hasta que cambie la contraseña
                ]);
                
                $viticulturistId = $viticulturist->id;
                
                // Crear relación WineryViticulturist
                WineryViticulturist::create([
                    'winery_id' => $this->winery_id ?: null,
                    'viticulturist_id' => $viticulturist->id,
                    'source' => WineryViticulturist::SOURCE_VITICULTURIST,
                    'parent_viticulturist_id' => $creator->id,
                    'assigned_by' => $creator->id,
                ]);
                
                // Generar PDF con credenciales
                $pdf = Pdf::loadView('pdf.viticulturist-credentials', [
                    'viticulturist' => $viticulturist,
                    'password' => $password,
                    'creator' => $creator,
                ]);
                
                // Guardar PDF temporalmente
                $tempDir = storage_path('app/temp');
                File::ensureDirectoryExists($tempDir);
                
                $pdfPath = $tempDir . '/credentials_' . $viticulturist->id . '_' . time() . '.pdf';
                $pdf->save($pdfPath);
                
                // Guardar path en sesión para descarga
                session(['viticulturist_credentials_pdf' => $pdfPath]);
                session(['viticulturist_created_id' => $viticulturist->id]);
                session(['viticulturist_created_name' => $viticulturist->name]);
            });
            
            // Guardar sesión antes de redirigir
            session()->save();
            
            session()->flash('message', 'Viticultor creado correctamente. Descarga el PDF con las credenciales.');
            return $this->redirect(route('viticulturist.personal.viticulturist.download-credentials', ['id' => $viticulturistId]), navigate: false);
        } catch (\Exception $e) {
            Log::error('Error al crear viticultor', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            session()->flash('error', 'Error al crear el viticultor. Por favor, intenta de nuevo.');
        }
    }

    public function render()
    {
        $user = Auth::user();
        $wineries = $user->wineries;
        
        return view('livewire.viticulturist.personal.viticulturist.create', [
            'wineries' => $wineries,
        ])->layout('layouts.app');
    }
}

