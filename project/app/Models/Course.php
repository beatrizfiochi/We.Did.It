<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['title', 'url', 'imageUrl', 'location', 'schedule', 'start_date', 'price', 'status'])]
class Course extends Model
{
    protected $table = 'courses';

    public $timestamps = false;

    public function newsletters()
    {
        return $this->belongsToMany(Newsletter::class, 'course_newsletter', 'course_id', 'newsletter_id');
    }
}
