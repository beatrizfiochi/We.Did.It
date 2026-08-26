<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CreateUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_see_the_create_user_form(): void
    {
        $response = $this->get(route('admin.users.create'));

        $response->assertRedirect(route('login'));
    }

    public function test_guests_cannot_create_users(): void
    {
        $response = $this->post(route('admin.users.store'), [
            'name' => 'Intruso',
            'email' => 'intruso@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect(route('login'));
        $this->assertDatabaseMissing('users', ['email' => 'intruso@example.com']);
        $this->assertGuest();
    }

    public function test_authenticated_users_can_see_the_create_user_form(): void
    {
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->get(route('admin.users.create'));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page->component('Admin/CreateUser'));
    }

    public function test_authenticated_users_can_create_other_users(): void
    {
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Novo Admin',
            'email' => 'novo@cesae.pt',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertDatabaseHas('users', [
            'name' => 'Novo Admin',
            'email' => 'novo@cesae.pt',
        ]);

        $response->assertRedirect(route('dashboard'));
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
