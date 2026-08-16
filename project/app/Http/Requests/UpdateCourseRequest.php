<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCourseRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // sem 'required': um PATCH pode enviar só o campo alterado
            'title' => ['string', 'max:255'],
            'url' => ['url', 'max:255'],
            'imageUrl' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'schedule' => ['nullable', 'string', 'max:255'],
            'start_date' => ['date'],
            'price' => ['numeric', 'min:0'],
            'status' => ['string', 'in:received,accepted,refused'],
            // newsletters onde esta oferta formativa deve aparecer
            'newsletter_ids' => ['sometimes', 'array'],
            'newsletter_ids.*' => ['integer', 'exists:newsletters,id'],
        ];
    }
}
