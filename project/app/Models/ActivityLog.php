<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['user_id', 'table_name', 'record_id', 'operation'])]
class ActivityLog extends Model
{
    use HasFactory;

    protected $table = 'logs';

    /**
     * Regista uma operação feita pelo utilizador autenticado sobre um registo.
     *
     * O nome da tabela e a chave vêm do próprio model, para não haver risco de
     * se escrever a tabela errada nas dezenas de sítios que chamam este método.
     *
     * Nos apagamentos, chamar depois do delete(): o Eloquent mantém os atributos
     * na instância, por isso o getKey() continua a devolver o id, e a coluna
     * record_id não é chave estrangeira, logo a linha do log sobrevive.
     *
     * @param  'created'|'updated'|'removed'  $operation  valores do enum da coluna
     */
    public static function record(Model $model, string $operation): self
    {
        return self::create([
            'user_id' => auth()->id(),
            'table_name' => $model->getTable(),
            'record_id' => $model->getKey(),
            'operation' => $operation,
        ]);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
