<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateNewsletterTestimonialsRequest extends FormRequest
{
    /**
     * Determina se o utilizador está autorizado a fazer este pedido.
     */
    public function authorize(): bool
    {
        return true;
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
