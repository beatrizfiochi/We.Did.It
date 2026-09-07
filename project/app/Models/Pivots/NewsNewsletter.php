<?php

namespace App\Models\Pivots;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Linha da pivot news_newsletter.
 *
 * Existe só para o cast do image_ids: sem um Pivot próprio, o
 * withPivot('image_ids') devolvia a string JSON crua (SCRUM-143).
 */
class NewsNewsletter extends Pivot
{
    protected $table = 'news_newsletter';

    protected $casts = [
        'image_ids' => 'array',
    ];
}
