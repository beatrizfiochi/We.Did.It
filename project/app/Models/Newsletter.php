<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['title', 'edition', 'date', 'period_start', 'period_end', 'path', 'status'])]
class Newsletter extends Model
{
    protected $table = 'newsletters';

    public function news()
    {
        return $this->belongsToMany(News::class, 'news_newsletter', 'newsletter_id', 'news_id')
            ->withPivot('order');
    }

    public function testimonials()
    {
        return $this->belongsToMany(Testimonial::class, 'newsletter_testimonial', 'newsletter_id', 'testimonial_id')
            ->withPivot('order');
    }

    public function courses()
    {
        return $this->belongsToMany(Course::class, 'course_newsletter', 'newsletter_id', 'course_id');
    }

    public function calendars()
    {
        return $this->belongsToMany(Calendar::class, 'calendar_newsletter', 'newsletter_id', 'calendar_id');
    }
}
