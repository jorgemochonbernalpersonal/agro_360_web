<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\SigpacCode;

class SigpacCodeFormat implements Rule
{
    protected $message = '';

    public function passes($attribute, $value)
    {
        try {
            SigpacCode::parseSigpacCode($value);
            return true;
        } catch (\InvalidArgumentException $e) {
            $this->message = $e->getMessage();
            return false;
        }
    }

    public function message()
    {
        return $this->message ?: 'El formato del código SIGPAC no es válido.';
    }
}

