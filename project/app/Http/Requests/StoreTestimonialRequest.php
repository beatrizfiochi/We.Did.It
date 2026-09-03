<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesImages;
use App\Models\Image;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreTestimonialRequest extends FormRequest
{
    use ValidatesImages;

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
            'title' => ['required', 'string', 'min:5', 'max:255'],
            'description' => ['required', 'string', 'min:100', 'max:1050'],
            // a opção "Nenhuma" do formulário envia string vazia
            'category_id' => ['nullable', 'exists:categories,id'],
            'images' => ['nullable', 'array', 'max:'.Image::MAX_POR_SUBMISSAO],
            ...$this->imageRules(),
            // honeypot: campo escondido que tem de vir vazio; os bots tendem a
            // preencher tudo o que encontram. Igual ao StoreNewsRequest.
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

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            ...$this->imageMessages(),
            'name.required' => 'O nome é obrigatório.',
            'email.required' => 'O email é obrigatório.',
            'email.email' => 'Indica um email válido.',
            'title.min' => 'O título deve ter entre 5 e 255 caracteres.',
            'title.max' => 'O título deve ter entre 5 e 255 caracteres.',
            'description.min' => 'A descrição deve ter entre 100 e 1050 caracteres.',
            'description.max' => 'A descrição deve ter entre 100 e 1050 caracteres.',
            'images.max' => 'Podes enviar no máximo '.Image::MAX_POR_SUBMISSAO.' imagens.',
            'terms_conditions.accepted' => 'É necessário aceitar a Política de Privacidade.',
            'image_rights.accepted' => 'É necessário autorizar a utilização da imagem.',
        ];
    }
}
