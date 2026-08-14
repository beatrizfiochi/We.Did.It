<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreTestimonialRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // formulário público, aberto a qualquer visitante
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * Os limites acompanham os do formulário do lado do cliente, para as
     * mensagens não se contradizerem.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // name e email são NOT NULL na tabela testimonials
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'title' => ['required', 'string', 'min:10', 'max:255'],
            'description' => ['required', 'string', 'min:100', 'max:1050'],
            // a opção "Nenhuma" do formulário envia string vazia
            'category_id' => ['nullable', 'exists:categories,id'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'O nome é obrigatório.',
            'email.required' => 'O email é obrigatório.',
            'email.email' => 'Indica um email válido.',
            'title.min' => 'O título deve ter entre 10 e 255 caracteres.',
            'title.max' => 'O título deve ter entre 10 e 255 caracteres.',
            'description.min' => 'A descrição deve ter entre 100 e 1050 caracteres.',
            'description.max' => 'A descrição deve ter entre 100 e 1050 caracteres.',
            'image.max' => 'A imagem deve ter no máximo 5 MB.',
            'image.mimes' => 'A imagem tem de ser um ficheiro JPG ou PNG.',
        ];
    }
}
