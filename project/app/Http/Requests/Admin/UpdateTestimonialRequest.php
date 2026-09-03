<?php

namespace App\Http\Requests\Admin;

use App\Models\Image;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTestimonialRequest extends FormRequest
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
     * Tal como no UpdateNewsRequest, o status não está aqui: só o approve()
     * e o refuse() mexem no estado.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'title' => ['required', 'string', 'min:5', 'max:255'],
            'description' => ['required', 'string', 'min:100', 'max:1050'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'images' => [
                'nullable',
                'array',
                // as que já lá estão contam: o limite é o total, não o do pedido
                'max:'.max(0, Image::MAX_POR_SUBMISSAO - $this->route('testimonial')->images()->count()),
            ],
            'images.*' => ['image', 'mimes:jpg,jpeg,png', 'max:5120'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'O título é obrigatório.',
            'title.min' => 'O título deve ter entre 5 e 255 caracteres.',
            'title.max' => 'O título deve ter entre 5 e 255 caracteres.',
            'description.required' => 'A descrição é obrigatória.',
            'description.min' => 'A descrição deve ter entre 100 e 1050 caracteres.',
            'description.max' => 'A descrição deve ter entre 100 e 1050 caracteres.',
            'images.max' => 'Esta submissão só pode ter '.Image::MAX_POR_SUBMISSAO.' imagens. Remove uma antes de acrescentar.',
        ];
    }
}
