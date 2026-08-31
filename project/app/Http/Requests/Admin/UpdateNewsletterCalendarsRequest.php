<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateNewsletterCalendarsRequest extends FormRequest
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
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'calendar_ids' => ['sometimes', 'array'],
            'calendar_ids.*' => ['integer', 'exists:calendars,id'],
        ];
    }
}
