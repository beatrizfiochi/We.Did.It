<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Concerns\ForbidsPublishedNewsletters;
use Illuminate\Foundation\Http\FormRequest;

class UpdateNewsletterCoursesRequest extends FormRequest
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
            'course_ids' => ['sometimes', 'array'],
            'course_ids.*' => ['integer', 'exists:courses,id'],
        ];
    }
}
