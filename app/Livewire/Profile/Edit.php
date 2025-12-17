<?php

namespace App\Livewire\Profile;

use App\Models\UserProfile;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class Edit extends Component
{
    // Información Personal
    public $name;
    public $email;
    
    // Cambio de Contraseña
    public $current_password = '';
    public $password = '';
    public $password_confirmation = '';
    
    // Información de Contacto
    public $address;
    public $city;
    public $postal_code;
    public $province;
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
            $this->province = $profile->province;
            $this->country = $profile->country;
            $this->phone = $profile->phone;
        }
    }

    public function updatePersonalInfo()
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . Auth::id()],
        ]);

        $user = Auth::user();
        $user->name = $this->name;
        $user->email = $this->email;
        $user->save();

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
            'province' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        $user = Auth::user();
        
        $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'address' => $this->address,
                'city' => $this->city,
                'postal_code' => $this->postal_code,
                'province' => $this->province,
                'country' => $this->country,
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
