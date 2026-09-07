<?php

namespace Tests\Feature\Admin;

use App\Http\Middleware\EnsureUserIsActive;
use App\Mail\NewSubmissionReceived;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Ativar e desativar contas de administrador (SCRUM-146).
 *
 * Não há eliminação de propósito: a tabela logs tem onDelete('cascade') no
 * user_id, por isso apagar um administrador levava com ele todo o histórico de
 * atividade — quem aprovou o quê, quem publicou o quê. Uma conta desativada
 * não entra e não recebe avisos, mas continua a dar nome ao que fez.
 */
class UserStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_deactivated_account_cannot_log_in(): void
    {
        $admin = User::factory()->create();
        $outro = User::factory()->create();

        $this->actingAs($admin)
            ->patch(route('admin.users.status', $outro))
            ->assertSessionHasNoErrors();

        $this->assertFalse((bool) $outro->fresh()->status);

        // o actingAs mantém a sessão aberta durante o teste todo: sem o
        // logout, o middleware de convidado reencaminha o POST ao /login
        // antes de ele chegar ao controlador, e o teste passava sem testar nada
        Auth::logout();

        $this->post('/login', [
            'email' => $outro->email,
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_reactivating_lets_the_account_log_in_again(): void
    {
        $admin = User::factory()->create();
        $outro = User::factory()->create(['status' => false]);

        $this->actingAs($admin)
            ->patch(route('admin.users.status', $outro))
            ->assertSessionHasNoErrors();

        $this->assertTrue((bool) $outro->fresh()->status);

        Auth::logout();

        $this->post('/login', [
            'email' => $outro->email,
            'password' => 'password',
        ])->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticated();
    }

    /**
     * Senão fica de fora e precisa de outra pessoa para voltar — e se for a
     * única, não há outra pessoa.
     */
    public function test_an_admin_cannot_deactivate_themselves(): void
    {
        $admin = User::factory()->create();
        User::factory()->create();

        $this->actingAs($admin)
            ->patch(route('admin.users.status', $admin))
            ->assertForbidden();

        $this->assertTrue((bool) $admin->fresh()->status);
    }

    /**
     * Sem administradores ativos ninguém volta a entrar em lado nenhum, e não
     * há como resolver pela aplicação — só com acesso direto à base de dados.
     *
     * Esta guarda é a segunda linha de defesa, e por isso o teste desliga o
     * EnsureUserIsActive de propósito. Com o middleware ligado o cenário não
     * existe: um desativado não chega a fazer pedido nenhum, e um ativo que
     * tente desativar o último ativo está a tentar desativar-se a si próprio,
     * o que a primeira guarda já apanha.
     *
     * Fica na mesma porque o middleware pode ser removido, ou uma rota pode
     * nascer fora do grupo web, e aí é isto que trava.
     */
    public function test_the_last_active_admin_cannot_be_deactivated(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();

        // o A desativa o B
        $this->actingAs($a)->patch(route('admin.users.status', $b));
        $this->assertFalse((bool) $b->fresh()->status);
        $this->assertSame(1, User::active()->count());

        // o B, já desativado, tenta desativar o A
        $this->withoutMiddleware(EnsureUserIsActive::class)
            ->actingAs($b->fresh())
            ->patch(route('admin.users.status', $a))
            ->assertForbidden();

        $this->assertTrue((bool) $a->fresh()->status);
        $this->assertSame(1, User::active()->count());
    }

    /**
     * O que a guarda acima deixou de precisar de cobrir: desativar alguém já
     * não lhe deixa a sessão aberta até ao fim do SESSION_LIFETIME.
     */
    public function test_an_open_session_ends_as_soon_as_the_account_is_deactivated(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();

        $this->actingAs($a)->patch(route('admin.users.status', $b));

        $this->actingAs($b->fresh())
            ->get(route('admin.news.index'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    /**
     * A razão de existir desta decisão: desativar em vez de apagar mantém o
     * registo de auditoria. Se alguém trocar isto por um destroy(), este teste
     * é o que trava.
     */
    public function test_deactivating_keeps_the_activity_history(): void
    {
        $admin = User::factory()->create();
        $outro = User::factory()->create();

        // atividade feita pela conta que vai ser desativada
        $this->actingAs($outro);
        ActivityLog::record(Category::create(['name' => 'Estágios']), 'created');
        $this->assertSame(1, ActivityLog::where('user_id', $outro->id)->count());

        $this->actingAs($admin)
            ->patch(route('admin.users.status', $outro))
            ->assertSessionHasNoErrors();

        $this->assertSame(
            1,
            ActivityLog::where('user_id', $outro->id)->count(),
            'O histórico de atividade desapareceu ao desativar a conta.'
        );
    }

    /**
     * Já funciona sozinho pelo scope active() do NotifiesManagers, mas é
     * metade do que "desativar" significa e não estava travado.
     */
    public function test_a_deactivated_account_stops_receiving_submission_notices(): void
    {
        Mail::fake();

        $admin = User::factory()->create();
        $outro = User::factory()->create();

        $this->actingAs($admin)->patch(route('admin.users.status', $outro));

        $this->post(route('news.store'), [
            'title' => 'Uma notícia com título suficientemente válido',
            'description' => str_repeat('Um parágrafo com bastante conteúdo. ', 5),
            'event_start_date' => '2026-05-12',
            'terms_conditions' => 'on',
        ])->assertSessionHasNoErrors();

        Mail::assertSent(
            NewSubmissionReceived::class,
            fn (NewSubmissionReceived $mail) => $mail->hasTo($admin->email)
                && ! $mail->hasTo($outro->email)
        );
    }

    public function test_guests_cannot_change_a_user_status(): void
    {
        $outro = User::factory()->create();

        $this->patch(route('admin.users.status', $outro))
            ->assertRedirect(route('login'));

        $this->assertTrue((bool) $outro->fresh()->status);
    }
}
