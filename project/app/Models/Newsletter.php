<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['title', 'edition', 'date', 'period_start', 'period_end', 'path', 'status'])]
class Newsletter extends Model
{
    use HasFactory;

    protected $table = 'newsletters';

    /**
     * status é boolean por decisão da estrutura inicial, mas o modelo de dados
     * (DataBase/Modelo_Dados.png) documenta dois estados: draft e published.
     * true = rascunho, que é o default da coluna e o estado em que nasce.
     */
    public const RASCUNHO = true;

    public const PUBLICADA = false;

    protected $appends = ['is_draft'];

    /**
     * O cast de status é obrigatório: a base de dados devolve
     * 1 ou 0, e 1 === true é falso em PHP. Sem ele, o is_draft dava sempre
     * false e todas as newsletters apareciam como publicadas.
     *
     * Os casts de data poupam o .slice(0, 10) que a agenda teve de fazer à mão
     * para alimentar um <input type="date">.
     */
    protected function casts(): array
    {
        return [
            'status' => 'boolean',
            'date' => 'date',
            'period_start' => 'date',
            'period_end' => 'date',
        ];
    }

    protected function isDraft(): Attribute
    {
        return Attribute::get(fn () => $this->status === self::RASCUNHO);
    }

    #[Scope]
    protected function drafts(Builder $query): void
    {
        $query->where('status', self::RASCUNHO);
    }

    #[Scope]
    protected function published(Builder $query): void
    {
        $query->where('status', self::PUBLICADA);
    }

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
