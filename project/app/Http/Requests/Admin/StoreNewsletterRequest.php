<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreNewsletterRequest extends FormRequest
{
    /**
     * Determina se o utilizador está autorizado a fazer este pedido.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Regras de validação aplicadas ao pedido.
     *
     * O status não entra: uma newsletter nasce sempre em rascunho, pelo
     * default da coluna. Publicar é a SCRUM-116. O path é preenchido pela
     * geração do PDF, na SCRUM-117.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:5', 'max:255'],
            'edition' => ['required', 'integer', 'min:1', Rule::unique('newsletters', 'edition')],
            'date' => ['required', 'date'],
            'period_start' => ['required', 'date'],
            'period_end' => ['required', 'date', 'after_or_equal:period_start'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'O título é obrigatório.',
            'title.string' => 'O título deve ser um texto.',
            'title.min' => 'O título deve ter entre 5 e 255 caracteres.',
            'title.max' => 'O título deve ter entre 5 e 255 caracteres.',

            'edition.required' => 'A edição é obrigatória.',
            'edition.integer' => 'A edição deve ser um número inteiro.',
            'edition.unique' => 'A edição escolhida já existe.',

            'date.required' => 'A data é obrigatória.',
            'date.date' => 'A data informada é inválida.',

            'period_start.required' => 'A data de início do período é obrigatória.',
            'period_start.date' => 'A data de início do período é inválida.',

            'period_end.required' => 'A data de fim do período é obrigatória.',
            'period_end.date' => 'A data de fim do período é inválida.',
            'period_end.after_or_equal' => 'A data de fim do período deve ser igual ou posterior à data de início.',

            'status.required' => 'O status é obrigatório.',
            'status.boolean' => 'O status deve ser verdadeiro ou falso.',
        ];
    }
}
