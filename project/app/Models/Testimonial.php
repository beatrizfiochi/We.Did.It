<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['category_id', 'name', 'email', 'title', 'description', 'image', 'status'])]
class Testimonial extends Model
{
    protected $table = 'testimonials';

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function newsletters()
    {
        return $this->belongsToMany(Newsletter::class, 'newsletter_testimonial', 'testimonial_id', 'newsletter_id')
            ->withPivot('order');
    }
}
