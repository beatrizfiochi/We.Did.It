<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateNewsletterTestimonialsRequest extends FormRequest
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
     * Só testemunhos aprovados entram na newsletter. A regra vive aqui, e não só
     * no ecrã, para que um pedido feito à mão ao endpoint não consiga associar
     * um testemunho por moderar ou recusado.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'testimonial_ids' => ['sometimes', 'array'],
            'testimonial_ids.*' => ['integer', Rule::exists('testimonials', 'id')->where('status', 'accepted')],
        ];
    }
}
