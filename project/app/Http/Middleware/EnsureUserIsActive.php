<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Fecha a sessão de quem foi desativado enquanto estava a trabalhar (SCRUM-146).
 *
 * O LoginRequest só verifica o status na entrada. Sem isto, desativar uma conta
 * não tirava o acesso a quem já tinha sessão aberta — ficava a trabalhar até ao
 * fim do SESSION_LIFETIME, que são 120 minutos. Apagar as linhas da tabela
 * sessions também não bastava: um "remember me" volta a autenticar sem passar
 * pelo LoginRequest.
 *
 * Não custa uma consulta nova: o guarda de sessão já carrega o utilizador em
 * cada pedido autenticado, e o HandleInertiaRequests já o usa em todas as
 * páginas para partilhar o auth.user. Aqui só se lê um campo do objeto que já
 * está em memória.
 */
class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && ! $request->user()->status) {
            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => 'Esta conta foi desativada. Fala com outro administrador.',
            ]);
        }

        return $next($request);
    }
}
