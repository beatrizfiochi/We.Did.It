<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name'])]
class Category extends Model
{
    protected $table = 'categories';

    public function testimonials()
    {
        return $this->hasMany(Testimonial::class, 'category_id');
    }

    public function news()
    {
        return $this->hasMany(News::class, 'category_id');
    }
}
