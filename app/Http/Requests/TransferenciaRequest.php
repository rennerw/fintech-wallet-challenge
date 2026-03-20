<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class TransferenciaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'para_user' => ['required', 'email', 'exists:users,email',function ($attribute, $value, $fail) {
                if ($value === $this->user()->email) {
                    $fail('O destinatário deve ser diferente do remetente.');
                }
            }],
            'valor' => ['required', 'numeric', 'gt:0', 'regex:/^\d+(\.\d{1,2})?$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'para_user.required' => 'E-mail do destinatário é obrigatório',
            'para_user.email' => 'E-mail inválido',
            'para_user.exists' => 'Usuário não encontrado',
            'valor.required' => 'Valor é obrigatório',
            'valor.numeric' => 'Valor deve ser numérico',
            'valor.gt' => 'Valor deve ser maior que zero',
            'valor.regex' => 'Valor deve ter no máximo 2 casas decimais',
        ];
    }
}
