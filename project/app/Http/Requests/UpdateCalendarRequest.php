<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCalendarRequest extends FormRequest
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
            'date' => ['date'],
            'title' => ['string', 'max:255'],
            // newsletters onde este evento deve aparecer
            'newsletter_ids' => ['sometimes', 'array'],
            'newsletter_ids.*' => ['integer', 'exists:newsletters,id'],
        ];
    }
}
