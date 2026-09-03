<?php

namespace App\Http\Requests;

use App\Models\Image;
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
            'images' => ['nullable', 'array', 'max:'.Image::MAX_POR_SUBMISSAO],
            'images.*' => ['image', 'mimes:jpg,jpeg,png', 'max:5120'],
            // 'status' => ['sometimes', 'required', 'string', 'in:received,approved,refused'],
            // honeypot: hidden field that must stay empty; bots tend to fill every field they find
            'website' => ['prohibited'],
            // Consentimentos exigidos pelo cliente. Não são guardados: servem
            // só para recusar a submissão se não vierem marcados. Sem estas
            // regras a exigência vivia apenas no JavaScript, e um POST direto
            // ao endpoint passava por cima dela.
            'terms_conditions' => ['accepted'],
            // exclude_without: sem imagem, a autorização nem sequer é avaliada.
            // Só 'accepted' não bastava — essa regra falha também quando o campo
            // está ausente, o que tornava a autorização obrigatória sempre.
            'image_rights' => ['exclude_without:images', 'accepted'],
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
            'terms_conditions.accepted' => 'É necessário aceitar a Política de Privacidade.',
            'images.max' => 'Podes enviar no máximo '.Image::MAX_POR_SUBMISSAO.' imagens.',
            'images.*.image' => 'Cada ficheiro tem de ser uma imagem.',
            'images.*.mimes' => 'As imagens têm de ser jpg, jpeg ou png.',
            'images.*.max' => 'Cada imagem deve ter no máximo 5MB.',
            'image_rights.accepted' => 'É necessário autorizar a utilização da imagem.',
        ];
    }
}
