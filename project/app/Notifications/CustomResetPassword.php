<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordBase;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Classe que permite criar um boilerplate para o email de recuperação da password
 */
class CustomResetPassword extends ResetPasswordBase
{
    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        return (new MailMessage)
            ->subject('Recuperar a palavra-passe')
            // busca o template definido no ficheiro com o conteudo escrito para o email
            ->markdown('emails.reset-password.resetPassword', [
                'url' => $url,
                'count' => config('auth.passwords.' . config('auth.defaults.passwords') . '.expire'),
            ]);
    }
}
