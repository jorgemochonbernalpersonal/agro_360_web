<?php

namespace App\Livewire\Concerns;

trait WithRoleBasedFields
{
    protected function getVisibleFields(): array
    {
        $user = auth()->user();
        
        $baseFields = ['name', 'description', 'area', 'active'];
        
        return match($user->role) {
            'admin' => array_merge($baseFields, [
                'winery_id',
                'viticulturist_id',
                'autonomous_community_id',
                'province_id',
                'municipality_id',
                'sigpac_use',
                'sigpac_code',
            ]),
            'supervisor' => array_merge($baseFields, [
                'winery_id',
                'viticulturist_id',
                'autonomous_community_id',
                'province_id',
                'municipality_id',
                'sigpac_use',
                'sigpac_code',
            ]),
            'winery' => array_merge($baseFields, [
                'viticulturist_id',
                'autonomous_community_id',
                'province_id',
                'municipality_id',
                'sigpac_use',
                'sigpac_code',
            ]),
            'viticulturist' => array_merge($baseFields, [
                'viticulturist_id',
                'autonomous_community_id',
                'province_id',
                'municipality_id',
                'sigpac_use',
                'sigpac_code',
            ]),
            default => $baseFields,
        };
    }
    
    public function isFieldVisible(string $field): bool
    {
        return in_array($field, $this->getVisibleFields());
    }
    
    public function canSelectWinery(): bool
    {
        return in_array(auth()->user()->role, ['admin', 'supervisor']);
    }
    
    public function canSelectViticulturist(): bool
    {
        $user = auth()->user();
        
        // Admin, supervisor y winery siempre pueden seleccionar
        if (in_array($user->role, ['admin', 'supervisor', 'winery'])) {
            return true;
        }
        
        // Viticultores solo pueden seleccionar si tienen viticultores creados
        if ($user->isViticulturist()) {
            return \App\Models\WineryViticulturist::editableBy($user)->exists();
        }
        
        return false;
    }
    
    public function canSelectLocation(): bool
    {
        return in_array(auth()->user()->role, ['admin', 'supervisor', 'winery', 'viticulturist']);
    }
    
    public function canSelectSigpac(): bool
    {
        return in_array(auth()->user()->role, ['admin', 'supervisor', 'winery', 'viticulturist']);
    }
}
