<?php

namespace App\Http\Controllers\Concerns;

use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Avisa os gestores por email. Partilhado pelos formulários públicos de
 * notícias e testemunhos (SCRUM-88).
 */
trait NotifiesManagers
{
    /**
     * Envia um aviso a todos os utilizadores ativos.
     *
     * Qualquer utilizador autenticado é gestor, por isso os destinatários vêm
     * da tabela users e não de uma variável de ambiente. Vai um email só, com
     * todos em To: — são colegas da mesma associação.
     *
     * O envio nunca pode derrubar a submissão: o registo já está gravado, e um
     * erro de SMTP mostraria um 500 a quem submeteu, que ficaria a pensar que
     * perdeu o que escreveu.
     */
    protected function notifyManagers(Mailable $mailable, ?int $recordId = null): void
    {
        $managers = User::active()->get();

        // com uma coleção vazia o Mail tenta enviar sem destinatários e o SMTP recusa
        if ($managers->isEmpty()) {
            return;
        }

        try {
            Mail::to($managers)->send($mailable);
        } catch (\Throwable $e) {
            Log::error('Falha ao enviar o aviso de nova submissão', [
                'mailable' => $mailable::class,
                'record_id' => $recordId,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
