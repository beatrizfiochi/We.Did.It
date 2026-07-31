<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['category_id', 'title', 'description', 'image', 'status'])]
class New extends Model
{
    protected $table = 'news';

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function newsletters()
    {
        return $this->belongsToMany(Newsletter::class, 'news_newsletter', 'news_id', 'newsletter_id')
            ->withPivot('order');
    }
}
