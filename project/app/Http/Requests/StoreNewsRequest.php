<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreNewsRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules()
    {
        return [
            'title' => ['required', 'string', 'min:5', 'max:255'],
            'description' => ['required', 'min:100', 'max:1050', 'string'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            //'status' => ['sometimes', 'required', 'string', 'in:received,approved,refused'],
            // honeypot: hidden field that must stay empty; bots tend to fill every field they find
            'website' => ['prohibited'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'O título é obrigatório.',
            'title.min' => 'O título deve ter entre 5 e 255 caracteres.',
            'title.max' => 'O título deve ter entre 5 e 255 caracteres.',

            'description.required' => 'A descrição é obrigatória.',
            'description.min' => 'A descrição deve ter entre 100 e 1050 caracteres.',
            'description.max' => 'A descrição deve ter entre 100 e 1050 caracteres.',
        ];
    }
}
