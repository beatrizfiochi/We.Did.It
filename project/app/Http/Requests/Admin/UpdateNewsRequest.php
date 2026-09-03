<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Concerns\ValidatesImages;
use App\Models\Image;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateNewsRequest extends FormRequest
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
     * Get the validation rules that apply to the request.
     *
     * O status fica de fora de propósito. O model tem-no no #[Fillable], por
     * isso incluí-lo aqui deixaria qualquer pedido de edição aprovar-se a si
     * mesmo. Mudar o estado é exclusivo dos endpoints approve() e refuse().
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // as que já lá estão contam: o limite é o total da submissão, não o
        // número de ficheiros deste pedido
        $existing = $this->route('news')->images()->count();
        $available = max(0, Image::MAX_POR_SUBMISSAO - $existing);

        return [
            'title' => ['required', 'string', 'min:5', 'max:255'],
            'description' => ['required', 'string', 'min:100', 'max:1050'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'images' => ['nullable', 'array', 'max:'.$available],
            ...$this->imageRules(),
        ];
    }

    /**
     * @return array<string, string>
     */
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
            'images.max' => 'Esta submissão só pode ter '.Image::MAX_POR_SUBMISSAO.' imagens. Remove uma antes de acrescentar.',
        ];
    }
}
