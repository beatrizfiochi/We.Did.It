<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Concerns\ForbidsPublishedNewsletters;
use App\Models\Image;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateNewsletterNewsRequest extends FormRequest
{
    use ForbidsPublishedNewsletters;

    /**
     * Só notícias aprovadas entram na newsletter. A regra vive aqui, e não só
     * no ecrã, para que um pedido feito à mão ao endpoint não consiga associar
     * uma notícia por moderar ou recusada.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'news_ids' => ['sometimes', 'array'],
            'news_ids.*' => ['integer', Rule::exists('news', 'id')->where('status', 'accepted')],

            // { news_id: [image_id, …] } — quais imagens de cada notícia saem
            // nesta edição (SCRUM-143). Que os ids sejam mesmo daquela notícia
            // é o controller que garante; aqui trata-se só do formato e do
            // limite de 3 por item.
            'image_ids' => ['sometimes', 'array'],
            'image_ids.*' => ['array', 'max:'.Image::MAX_POR_SUBMISSAO_NOTICIAS],
            'image_ids.*.*' => ['integer'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'image_ids.*.max' => 'Cada notícia pode sair com no máximo '.Image::contagem(Image::MAX_POR_SUBMISSAO_NOTICIAS).'.',
        ];
    }
}
