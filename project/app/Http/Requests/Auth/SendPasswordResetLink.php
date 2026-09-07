<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class SendPasswordResetLink extends FormRequest
{
    // não há sessão nesta fase
    public function authorize(): bool
    {
        return true;
    }

    // ResetPasswordRequest
    public function rules(): array
    {
        return ['email' => ['required', 'email']];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'O email é obrigatório.',
            'email.email' => 'O email inserido é inválido.',
        ];
    }
}
