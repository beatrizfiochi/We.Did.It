<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Concerns\ForbidsPublishedNewsletters;
use App\Models\Image;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateNewsletterTestimonialsRequest extends FormRequest
{
    use ForbidsPublishedNewsletters;

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

            // { testimonial_id: [image_id, …] } — quais imagens saem nesta
            // edição (SCRUM-143). Ver UpdateNewsletterNewsRequest.
            'image_ids' => ['sometimes', 'array'],
            'image_ids.*' => ['array', 'max:'.Image::MAX_POR_SUBMISSAO],
            'image_ids.*.*' => ['integer'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'image_ids.*.max' => 'Cada testemunho pode sair com no máximo '.Image::MAX_POR_SUBMISSAO.' imagens.',
        ];
    }
}
