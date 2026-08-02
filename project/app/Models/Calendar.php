<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['date', 'title'])]
class Calendar extends Model
{
    use HasFactory;

    protected $table = 'calendars';

    public function newsletters()
    {
        return $this->belongsToMany(Newsletter::class, 'calendar_newsletter', 'calendar_id', 'newsletter_id');
    }
}
