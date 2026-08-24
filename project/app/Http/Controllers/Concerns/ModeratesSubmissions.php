<?php

namespace App\Http\Controllers\Concerns;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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

    /**
     * Substitui a imagem de um registo, apagando a anterior do disco.
     *
     * Sem isto, cada edição com imagem nova deixava a antiga esquecida em
     * storage/app/public a ocupar espaço para sempre.
     *
     * @param  array<string, mixed>  $data  dados já validados
     * @return array<string, mixed>
     */
    protected function replaceImage(array $data, Request $request, Model $model, string $folder): array
    {
        if (! $request->hasFile('image')) {
            // sem ficheiro novo, a imagem atual mantém-se
            unset($data['image']);

            return $data;
        }

        if ($model->image) {
            Storage::disk('public')->delete($model->image);
        }

        $data['image'] = $request->file('image')->store($folder, 'public');

        return $data;
    }
}
