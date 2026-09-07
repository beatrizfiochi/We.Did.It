<?php

namespace App\Models\Pivots;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Linha da pivot newsletter_testimonial.
 *
 * Igual ao NewsNewsletter: só existe pelo cast do image_ids (SCRUM-143).
 */
class NewsletterTestimonial extends Pivot
{
    protected $table = 'newsletter_testimonial';

    protected $casts = [
        'image_ids' => 'array',
    ];
}
