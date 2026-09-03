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

    public const MAX_POR_SUBMISSAO = 3;

    /** Em kilobytes, que é a unidade da regra max: do Laravel. */
    public const MAX_KB_POR_FICHEIRO = 5120;
}
