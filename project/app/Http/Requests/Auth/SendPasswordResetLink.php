<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class SendPasswordResetLink extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    } // não há sessão nesta fase


    // ResetPasswordRequest
    public function rules(): array
    {
        return ['email' => ['required', 'email', 'exists:users,email']];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'O email é obrigatório.',
            'email.email' => 'O email inserido é inválido.',
            'email.exists' => 'Não existe nenhuma conta com este email.'
        ];
    }
}
