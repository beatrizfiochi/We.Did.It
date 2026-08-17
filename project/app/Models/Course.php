<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['title', 'url', 'imageUrl', 'location', 'schedule', 'start_date', 'price', 'status'])]
class Course extends Model
{
    use HasFactory;

    protected $table = 'courses';

    public $timestamps = false;

    public function newsletters()
    {
        return $this->belongsToMany(Newsletter::class, 'course_newsletter', 'course_id', 'newsletter_id');
    }

    /**
     * start_date é uma string livre vinda da API externa ("2026-09-15", mas também
     * "A anunciar"). Devolve a data para ordenação cronológica; o que não for
     * legível vai para o fim da lista em vez de rebentar.
     */
    protected function startDateForSorting(): Attribute
    {
        return Attribute::get(fn () => rescue(
            fn () => Carbon::parse($this->start_date),
            fn () => Carbon::create(9999, 12, 31),
            report: false,
        ));
    }
}
