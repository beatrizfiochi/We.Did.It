<?php

namespace App\Models;

use App\Models\Pivots\NewsNewsletter;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['category_id', 'title', 'description', 'image', 'status', 'event_start_date', 'event_end_date'])]
class News extends Model
{
    use HasFactory;

    protected $table = 'news';

    protected $casts = [
        'event_start_date' => 'date',
        'event_end_date' => 'date',
    ];

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
        return $this->belongsToMany(Newsletter::class, 'news_newsletter', 'news_id', 'newsletter_id')
            ->using(NewsNewsletter::class)
            ->withPivot('order', 'image_ids');
    }
}
