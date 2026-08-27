<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateNewsletterNewsRequest extends FormRequest
{
    /**
     * Determina se o utilizador está autorizado a fazer este pedido.
     */
    public function authorize(): bool
    {
        return true;
    }

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
        ];
    }
}
