<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Cria o primeiro administrador.
     *
     * Como não existe registo público, é este seeder que dá a primeira
     * conta com que se entra na aplicação. A partir dela, os restantes
     * administradores são criados pela interface.
     */
    public function run(): void
    {
        $email = env('ADMIN_EMAIL', 'admin@example.com');

        if (User::where('email', $email)->exists()) {
            $this->command->warn("Administrador {$email} já existe. Nada foi alterado.");

            return;
        }

        $admin = new User();
        $admin->name = env('ADMIN_NAME', 'Administrador');
        $admin->email = $email;
        $admin->password = Hash::make(env('ADMIN_PASSWORD', 'password'));
        $admin->email_verified_at = now();
        $admin->save();

        $this->command->info("Administrador criado: {$email}");
    }
}
