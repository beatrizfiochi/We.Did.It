<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesImages;
use App\Models\Image;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreNewsRequest extends FormRequest
{
    use ValidatesImages;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Fim igual ao início é um evento de um dia, e um dia único guarda o fim a
     * null — é isso que distingue os dois casos em todo o lado, sem coluna a
     * dizer qual é qual (SCRUM-145).
     *
     * O formulário nunca produz este estado, porque deixa o campo vazio. Um
     * pedido feito à mão ao endpoint produzia, e o after_or_equal aceitava:
     * ficava gravado fim = início, e o cartão da newsletter escrevia
     * "12 a 12 de maio de 2026".
     */
    protected function prepareForValidation(): void
    {
        if ($this->event_end_date && $this->event_end_date === $this->event_start_date) {
            $this->merge(['event_end_date' => null]);
        }
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
            ...$this->imageRules(),
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
            'event_start_date' => ['required', 'date', 'before_or_equal:today'],
            'event_end_date' => ['nullable', 'date', 'after_or_equal:event_start_date'],
        ];
    }

    public function messages(): array
    {
        return [
            ...$this->imageMessages(),
            'title.required' => 'O título é obrigatório.',
            'title.min' => 'O título deve ter entre 5 e 255 caracteres.',
            'title.max' => 'O título deve ter entre 5 e 255 caracteres.',

            'description.required' => 'A descrição é obrigatória.',
            'description.min' => 'A descrição deve ter entre 100 e 1050 caracteres.',
            'description.max' => 'A descrição deve ter entre 100 e 1050 caracteres.',
            'terms_conditions.accepted' => 'É necessário aceitar a Política de Privacidade.',
            'images.max' => 'Podes enviar no máximo '.Image::MAX_POR_SUBMISSAO.' imagens.',
            'image_rights.accepted' => 'É necessário autorizar a utilização da imagem.',

            'event_start_date.required' => 'A data do evento é obrigatória.',
            'event_start_date.date' => 'A data do evento é inválida.',
            'event_start_date.before_or_equal' => 'A data do evento não pode ser no futuro.',
            'event_end_date.date' => 'A data de fim é inválida.',
            'event_end_date.after_or_equal' => 'A data de fim não pode ser anterior à data do evento.',
        ];
    }
}
