<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Concerns\ForbidsPublishedNewsletters;
use Illuminate\Foundation\Http\FormRequest;

class UpdateNewsletterCalendarsRequest extends FormRequest
{
    use ForbidsPublishedNewsletters;

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
