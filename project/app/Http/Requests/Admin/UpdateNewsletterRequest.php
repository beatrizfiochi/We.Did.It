<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateNewsletterRequest extends FormRequest
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
     * Regras de validação aplicadas ao pedido.
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:5', 'max:255'],
            'edition' => ['required', 'integer', 'min:1', Rule::unique('newsletters', 'edition')->ignore($this->route('newsletter'))],
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
        ];
    }
}
