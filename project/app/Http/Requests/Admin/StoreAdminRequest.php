<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;

class StoreAdminRequest extends FormRequest
{
    /**
     * Determina se o utilizador está autorizado a fazer este pedido.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Regras de validação aplicadas ao pedido.
     *
     * O domínio do email é requisito do cliente: as contas ficam restritas a
     *
     * @cesae.pt e @cesaedigital.pt. O @ no início de cada domínio é essencial —
     * sem ele, ana@notcesae.pt passaria.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'email' => [
                'required', 'string', 'lowercase', 'email', 'max:255',
                'ends_with:@cesae.pt,@cesaedigital.pt',
                Rule::unique(User::class),
            ],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O nome é obrigatório.',
            'name.string' => 'O nome deve ser um texto.',
            'name.min' => 'O nome deve ter entre 2 e 255 caracteres.',
            'name.max' => 'O nome deve ter entre 2 e 255 caracteres.',

            'email.required' => 'O email é obrigatório.',
            'email.lowercase' => 'O email deve ter apenas caracteres minúsculos.',
            'email.email' => 'O email inserido é inválido.',
            'email.max' => 'O email não pode ter mais de 255 caracteres.',
            'email.ends_with' => 'O email tem de ser do domínio @cesae.pt ou @cesaedigital.pt.',
            'email.unique' => 'Já existe uma conta com este email',

            'password.required' => 'A palavra-chave é obrigatória.',
            'password.confirmed' => 'A palavra-chave e a confirmação não coincidem.',
            'password.min' => 'A palavra-chave deve conter no mínimo 8 caracteres.',
        ];
    }
}
