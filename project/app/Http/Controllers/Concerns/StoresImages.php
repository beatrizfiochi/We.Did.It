<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Guarda as imagens de uma submissão (SCRUM-140).
 *
 * Serve notícias e testemunhos: o que muda é a pasta no disco. O model tem
 * de ter a relação images() e a coluna image.
 */
trait StoresImages
{
    /**
     * @param  array<int, UploadedFile>  $files  já validados
     */
    protected function storeImages(Model $model, array $files, string $folder): void
    {
        $paths = [];

        try {
            foreach (array_values($files) as $file) {
                $paths[] = $file->store($folder, 'public');
            }

            DB::transaction(function () use ($model, $paths) {
                // continua a numeração em vez de recomeçar: assim isto também
                // serve para acrescentar imagens a um registo que já tem
                $order = (int) $model->images()->max('order');

                foreach ($paths as $path) {
                    $model->images()->create(['path' => $path, 'order' => ++$order]);
                }
            });

            $this->syncImageMirror($model);
        } catch (Throwable $e) {
            // os ficheiros já estão no disco e as linhas não: sem isto ficavam
            // órfãos para sempre, sem nada na base de dados a apontar-lhes
            Storage::disk('public')->delete($paths);

            throw $e;
        }
    }

    /**
     * Põe a coluna image a apontar à primeira imagem, ou a null se não houver.
     *
     * A coluna antiga continua a ser o que sete ecrãs leem. Enquanto isso for
     * verdade, tem de acompanhar a tabela nova a cada escrita.
     */
    protected function syncImageMirror(Model $model): void
    {
        $model->update([
            'image' => $model->images()->orderBy('order')->orderBy('id')->value('path'),
        ]);
    }
}
