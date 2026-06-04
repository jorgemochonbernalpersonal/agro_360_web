<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;

class MobileResetPasswordNotification extends ResetPassword
{
    protected function resetUrl($notifiable): string
    {
        return route('password.reset', ['token' => $this->token]).'?platform=mobile';
    }
}
