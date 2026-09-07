<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    /**
     * O Breeze traz um "Delete Account" no Perfil, e este projeto removeu-o.
     *
     * O ciclo de vida dos administradores é gerido por outro administrador
     * (SCRUM-146): desativa-se, com confirmação e com guardas, e o histórico
     * de atividade sobrevive. O destroy do Breeze chamava $user->delete(), e
     * como o logs.user_id tem onDelete('cascade'), apagava com ele o registo
     * de quem aprovou e publicou o quê — e sem passar pela guarda do último
     * administrador ativo.
     *
     * Este teste existe para a rota não voltar sem essa conversa.
     */
    public function test_there_is_no_self_service_account_deletion(): void
    {
        $this->assertFalse(
            Route::has('profile.destroy'),
            'A eliminação da própria conta voltou: ver SCRUM-146 antes de a repor.'
        );

        // 405 e não 404: o /profile continua a existir para ver e editar,
        // só deixou de aceitar o DELETE
        $this->actingAs(User::factory()->create())
            ->delete('/profile', ['password' => 'password'])
            ->assertMethodNotAllowed();
    }
}
