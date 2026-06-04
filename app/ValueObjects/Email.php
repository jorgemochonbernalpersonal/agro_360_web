<?php

namespace App\ValueObjects;

class Email
{
    public function __construct(
        public readonly string $address
    ) {
        if (! filter_var($address, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException("Email inválido: {$address}");
        }
    }

    /**
     * Convertir a string
     */
    public function __toString(): string
    {
        return $this->address;
    }

    /**
     * Crear desde string
     */
    public static function from(string $address): self
    {
        return new self(strtolower(trim($address)));
    }

    /**
     * Obtener dominio del email
     */
    public function domain(): string
    {
        return substr($this->address, strpos($this->address, '@') + 1);
    }

    /**
     * Obtener nombre de usuario (parte local)
     */
    public function localPart(): string
    {
        return substr($this->address, 0, strpos($this->address, '@'));
    }

    /**
     * Verificar si es del dominio especificado
     */
    public function isFromDomain(string $domain): bool
    {
        return strtolower($this->domain()) === strtolower($domain);
    }

    /**
     * Ofuscar email para mostrar públicamente
     */
    public function obfuscate(): string
    {
        $parts = explode('@', $this->address);
        $local = $parts[0];
        $domain = $parts[1];

        if (strlen($local) <= 2) {
            return substr($local, 0, 1).'***@'.$domain;
        }

        return substr($local, 0, 2).'***@'.$domain;
    }
}
