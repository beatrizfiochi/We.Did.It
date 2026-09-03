<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\StoresImages;
use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Image;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{
    use StoresImages;

    /**
     * Remove uma imagem de uma notícia ou testemunho (SCRUM-140).
     *
     * A linha sai da base de dados primeiro, dentro da mesma transação que
     * recalcula a coluna espelho; o ficheiro só é apagado do disco depois,
     * já fora dela. Ao contrário, uma falha a meio deixava uma imagem que a
     * base de dados diz existir mas o disco já não tem.
     */
    public function destroy(Image $image): RedirectResponse
    {
        $model = $image->imageable;
        $path = $image->path;

        DB::transaction(function () use ($image, $model) {
            $image->delete();

            $this->syncImageMirror($model);
        });

        Storage::disk('public')->delete($path);

        ActivityLog::record($model, 'updated');

        return back()->with('success', 'Imagem removida.');
    }
}
