<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAdminRequest;
use App\Mail\AdminWelcome;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Lista os administradores, por ordem alfabética.
     */
    public function index(): Response
    {
        return Inertia::render('Admin/Users/Index', [
            'users' => User::orderBy('name')->get(['id', 'name', 'email', 'status', 'created_at']),
        ]);
    }

    /**
     * Store a new administrator account created from the admin area.
     *
     * @throws ValidationException
     */
    public function store(StoreAdminRequest $request): RedirectResponse
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // user_id é quem criou; record_id é o administrador novo
        ActivityLog::record($user, 'created');

        // o envio nunca pode derrubar a criação: a conta já está gravada, e um
        // erro de SMTP mostrava um 500 a quem acabou de a criar, que ficaria sem
        // saber se a conta existe. Mesmo raciocínio do NotifiesManagers, mas aqui
        // o destinatário é um só, e esse trait envia sempre para todos os gestores.
        try {
            Mail::to($user)->send(new AdminWelcome($user->name, auth()->user()->name));
        } catch (\Throwable $e) {
            Log::error('Falha ao enviar as boas-vindas ao novo administrador', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }

        event(new Registered($user));

        return back()->with('success', 'Administrador criado com sucesso.');
    }

    /**
     * Ativa ou desativa uma conta de administrador (SCRUM-146).
     *
     * Não há eliminação: a tabela logs tem onDelete('cascade') no user_id, por
     * isso apagar um administrador levava com ele todo o histórico de atividade
     * — quem aprovou o quê, quem publicou o quê. Uma conta desativada não entra
     * e não recebe avisos, mas continua a dar nome às operações que fez.
     */
    public function toggleStatus(Request $request, User $user): RedirectResponse
    {
        abort_if($user->is($request->user()), 403, 'Não podes desativar a tua própria conta.');

        if ($user->status && User::active()->count() === 1) {
            abort(403, 'Tem de ficar pelo menos um administrador ativo.');
        }

        $user->update(['status' => ! $user->status]);

        ActivityLog::record($user, 'updated');

        return back()->with('success', $user->status
            ? 'Administrador ativado.'
            : 'Administrador desativado.');
    }
}
