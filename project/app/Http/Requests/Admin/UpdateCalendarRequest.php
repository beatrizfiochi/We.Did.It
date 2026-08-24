<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCalendarRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * O 'sometimes' deixa passar uma atualização parcial (ex.: só o date) sem
     * apagar os restantes campos; o 'required' a seguir só entra em ação
     * quando o campo vem no pedido mas vazio.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'date' => ['sometimes', 'required', 'date'],
            // newsletters onde este evento deve aparecer
            'newsletter_ids' => ['sometimes', 'array'],
            'newsletter_ids.*' => ['integer', 'exists:newsletters,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'O título do evento é obrigatório.',
            'date.required' => 'A data do evento é obrigatória.',
            'date.date' => 'Indica uma data válida.',
        ];
    }
}
