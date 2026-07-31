<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['user_id', 'table_name', 'record_id', 'operation'])]
class ActivityLog extends Model
{
    protected $table = 'logs';

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
