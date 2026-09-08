<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['path', 'order'])]
class Image extends Model
{
    protected $table = 'images';

    public function imageable()
    {
        return $this->morphTo();
    }

    public const MAX_POR_SUBMISSAO_NOTICIAS = 3;

    public const MAX_POR_SUBMISSAO_TESTEMUNHOS = 1;

    /**
     * "uma imagem" ou "3 imagens", conforme o número.
     *
     * Desde que os testemunhos passaram a aceitar uma só, interpolar a
     * constante direto nas mensagens dava "no máximo 1 imagens".
     */
    public static function contagem(int $n): string
    {
        return $n === 1 ? 'uma imagem' : $n.' imagens';
    }

    /** Em kilobytes, que é a unidade da regra max: do Laravel. */
    public const MAX_KB_POR_FICHEIRO = 5120;
}
