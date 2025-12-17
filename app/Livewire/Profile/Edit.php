<?php

namespace App\Livewire\Profile;

use App\Models\UserProfile;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class Edit extends Component
{
    use WithFileUploads;
    
    // Información Personal
    public $name;
    public $email;
    public $profile_image;
    public $current_profile_image;
    
    // Cambio de Contraseña
    public $current_password = '';
    public $password = '';
    public $password_confirmation = '';
    
    // Información de Contacto
    public $address;
    public $city;
    public $postal_code;
    public $province_id;
    public $country = 'España';
    public $phone;
    
    // Control de Tabs
    public $activeTab = 'personal';

    public function mount()
    {
        $user = Auth::user();
        
        // Cargar datos del usuario
        $this->name = $user->name;
        $this->email = $user->email;
        
        // Cargar perfil si existe
        $profile = $user->profile;
        if ($profile) {
            $this->address = $profile->address;
            $this->city = $profile->city;
            $this->postal_code = $profile->postal_code;
            $this->province_id = $profile->province_id;
            $this->country = $profile->country ?? 'España';
            $this->phone = $profile->phone;
            $this->current_profile_image = $profile->profile_image;
        }
    }
    
    public function getProvincesProperty()
    {
        return \App\Models\Province::orderBy('name')->get();
    }

    public function updatePersonalInfo()
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . Auth::id()],
            'profile_image' => ['nullable', 'image', 'max:2048'], // Max 2MB
        ]);

        $user = Auth::user();
        $user->name = $this->name;
        $user->email = $this->email;
        $user->save();

        // Manejar subida de imagen
        if ($this->profile_image) {
            // Eliminar imagen anterior si existe
            if ($user->profile && $user->profile->profile_image) {
                Storage::disk('public')->delete($user->profile->profile_image);
            }
            
            // Guardar nueva imagen
            $path = $this->profile_image->store('profile-images', 'public');
            
            // Actualizar o crear perfil con la imagen
            $user->profile()->updateOrCreate(
                ['user_id' => $user->id],
                ['profile_image' => $path]
            );
            
            $this->current_profile_image = $path;
            $this->profile_image = null; // Reset el input
        }

        session()->flash('message', 'Información personal actualizada correctamente.');
        $this->dispatch('profile-updated');
    }

    public function updatePassword()
    {
        $this->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = Auth::user();
        $user->password = Hash::make($this->password);
        $user->save();

        // Limpiar campos
        $this->current_password = '';
        $this->password = '';
        $this->password_confirmation = '';

        session()->flash('password_message', 'Contraseña actualizada correctamente.');
    }

    public function updateContactInfo()
    {
        $this->validate([
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:10',
            'province_id' => 'nullable|exists:provinces,id',
            'phone' => 'nullable|string|max:20',
        ]);

        $user = Auth::user();
        
        $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'address' => $this->address,
                'city' => $this->city,
                'postal_code' => $this->postal_code,
                'province_id' => $this->province_id,
                'country' => 'España', // Siempre España
                'phone' => $this->phone,
            ]
        );

        session()->flash('contact_message', 'Información de contacto actualizada correctamente.');
    }

    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
        // Limpiar mensajes flash al cambiar de tab
        session()->forget(['message', 'password_message', 'contact_message']);
    }

    public function render()
    {
        return view('livewire.profile.edit');
    }
}
