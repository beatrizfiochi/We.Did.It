<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        // toMailUsing permite customizar um novo corpo/conteudo do email enviado
        // receives the user (notifiable) adn the token
        ResetPassword::toMailUsing((function ($notifiale, $token) {
            $url = url(route('password.reset', [
                'token' => $token,
                'email' => $notifiale->getEmailForPasswordReset(),
            ], false));

            return (new MailMessage)
                ->subject('Recuperar a palavra-passe')
                ->markdown('emails.reset-password.resetPassword', [
                    'url' => $url,
                    'count' => config('auth.passwords.'.config('auth.defaults.passwords').'.expire'),
                ]);
        }));
    }
}
