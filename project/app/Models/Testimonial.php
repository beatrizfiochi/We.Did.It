<?php

namespace App\Models;

use App\Models\Pivots\NewsletterTestimonial;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['category_id', 'name', 'email', 'title', 'description', 'image', 'status'])]
class Testimonial extends Model
{
    use HasFactory;

    protected $table = 'testimonials';

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function images()
    {
        return $this->morphMany(Image::class, 'imageable')
            ->orderBy('order')
            ->orderBy('id');
    }

    public function newsletters()
    {
        return $this->belongsToMany(Newsletter::class, 'newsletter_testimonial', 'testimonial_id', 'newsletter_id')
            ->using(NewsletterTestimonial::class)
            ->withPivot('order', 'image_ids');
    }
}
