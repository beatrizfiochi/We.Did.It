<?php

namespace App\Http\Controllers\Concerns;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;

/**
 * Partilhado pela moderação de notícias e testemunhos (SCRUM-86): as duas
 * seguem exatamente as mesmas regras, muda só o model.
 */
trait ModeratesSubmissions
{
    /**
     * Muda o estado de uma submissão e regista a operação.
     *
     * Os valores possíveis são os do enum da migration — 'received',
     * 'accepted' e 'refused', em inglês. Um valor fora destes dá erro de
     * MySQL, não de validação.
     */
    protected function changeStatus(Model $model, string $status, string $message): RedirectResponse
    {
        $model->update(['status' => $status]);

        ActivityLog::record($model, 'updated');

        return back()->with('success', $message);
    }
}
