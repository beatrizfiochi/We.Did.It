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
}
