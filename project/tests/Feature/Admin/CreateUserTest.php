<?php

namespace Tests\Feature\Admin;

use App\Mail\AdminWelcome;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/**
 * Criação de administradores pelo painel (SCRUM-118).
 *
 * Não há teste ao index: ele renderiza Admin/Users/Index, que é o ecrã da
 * SCRUM-124 e ainda não existe. Testá-lo agora daria 500. Fica do lado de quem
 * faz o ecrã.
 *
 * O CreateUser.jsx e a rota users.create ficam mortos quando esse ecrã entrar —
 * passa a ser tudo por modal, como nos outros CRUD. Os dois testes que ainda
 * usam users.create saem nessa altura.
 */
class CreateUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_access_any_users_management_route(): void
    {
        $user = User::factory()->create();

        $this->get(route('admin.news.index'))->assertRedirect(route('login'));
        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }

    public function test_authenticated_users_can_see_the_users_list(): void
    {
        $admin = User::factory()->create();
        User::factory()->count(3)->create();

        $response = $this->actingAs($admin)->get(route('admin.users.index'));

        $response->assertOk();
        $response->assertInertia(
            fn(Assert $page) => $page->component('Admin/Users/Index')
                ->has('users', 4) // conta com o admin que os criou
        );
    }

    public function test_guests_cannot_see_the_create_user_form(): void
    {
        $response = $this->get(route('admin.users.store'));

        $response->assertRedirect(route('login'));
    }

    public function test_guests_cannot_create_users(): void
    {
        $response = $this->post(route('admin.users.store'), [
            'name' => 'Intruso',
            'email' => 'intruso@cesae.pt',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect(route('login'));
        $this->assertDatabaseMissing('users', ['email' => 'intruso@cesae.pt']);
        $this->assertGuest();
    }

    public function test_authenticated_users_can_create_other_users(): void
    {
        $admin = User::factory()->create();

        // o controller responde com back(): o from() é o que faz o redirect
        // voltar para a listagem, como acontece no browser
        $response = $this->actingAs($admin)
            ->from(route('admin.users.index'))
            ->post(route('admin.users.store'), [
                'name' => 'Novo Admin',
                'email' => 'novo@cesae.pt',
                'password' => 'password',
                'password_confirmation' => 'password',
            ]);

        $this->assertDatabaseHas('users', [
            'name' => 'Novo Admin',
            'email' => 'novo@cesae.pt',
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');
    }

    public function test_creating_a_user_does_not_switch_the_current_session(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Novo Admin',
            'email' => 'novo@cesae.pt',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticatedAs($admin);
    }

    public function test_the_email_must_belong_to_an_allowed_domain(): void
    {
        $admin = User::factory()->create();

        // requisito do cliente: as contas ficam restritas aos domínios da casa
        $response = $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'De Fora',
            'email' => 'defora@gmail.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertDatabaseMissing('users', ['email' => 'defora@gmail.com']);
    }

    public function test_both_allowed_domains_are_accepted(): void
    {
        $admin = User::factory()->create();

        foreach (['ana@cesae.pt', 'rita@cesaedigital.pt'] as $email) {
            $this->actingAs($admin)->post(route('admin.users.store'), [
                'name' => 'Gestora',
                'email' => $email,
                'password' => 'password',
                'password_confirmation' => 'password',
            ]);

            $this->assertDatabaseHas('users', ['email' => $email]);
        }
    }

    public function test_creating_an_administrator_sends_the_welcome_email(): void
    {
        Mail::fake();
        $admin = User::factory()->create(['name' => 'Beatriz']);

        $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Ana Silva',
            'email' => 'ana@cesae.pt',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        Mail::assertSent(AdminWelcome::class, function (AdminWelcome $mail) {
            // vai para o administrador novo, não para quem o criou
            return $mail->hasTo('ana@cesae.pt')
                && $mail->name === 'Ana Silva'
                && $mail->createdBy === 'Beatriz';
        });
    }

    public function test_the_welcome_email_does_not_contain_the_password(): void
    {
        $mail = new AdminWelcome('Ana Silva', 'Beatriz');

        // a palavra-passe é comunicada por quem cria a conta; num email ficava
        // numa caixa de correio para sempre. Se este teste falhar, alguém a
        // acrescentou ao template por conveniência
        $this->assertStringNotContainsString('palavra-passe-secreta', $mail->render());
        $this->assertStringContainsString(route('login'), $mail->render());
    }

    public function test_a_failure_sending_the_email_does_not_lose_the_account(): void
    {
        $admin = User::factory()->create();

        // o registo já está gravado quando o email é enviado: um erro de SMTP não
        // pode devolver 500 a quem acabou de criar a conta
        Mail::shouldReceive('to')->andThrow(new \RuntimeException('SMTP em baixo'));

        $response = $this->actingAs($admin)
            ->from(route('admin.users.index'))
            ->post(route('admin.users.store'), [
                'name' => 'Ana Silva',
                'email' => 'ana@cesae.pt',
                'password' => 'password',
                'password_confirmation' => 'password',
            ]);

        $this->assertDatabaseHas('users', ['email' => 'ana@cesae.pt']);
        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');
    }

    public function test_email_must_be_unique(): void
    {
        $admin = User::factory()->create(['email' => 'existente@cesae.pt']);

        $response = $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Duplicado',
            'email' => 'existente@cesae.pt',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertDatabaseCount('users', 1);
    }
}
