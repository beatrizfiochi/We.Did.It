<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateNewsletterNewsRequest extends FormRequest
{
    /**
     * Determina se o utilizador está autorizado a fazer este pedido.
     *
     * Uma newsletter publicada não se altera (SCRUM-116). A verificação vive
     * aqui e não no controller porque o authorize() corre antes da validação:
     * no controller, o pedido morria primeiro nas regras e devolvia um erro de
     * formulário em vez do 403 que a situação é.
     */
    public function authorize(): bool
    {
        return $this->route('newsletter')->isEditable();
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
