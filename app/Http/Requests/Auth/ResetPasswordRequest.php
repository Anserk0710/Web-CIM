<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;

class ResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => Str::lower(
                trim(
                    (string) $this->input(
                        'email',
                    ),
                ),
            ),
        ]);
    }

    public function rules(): array
    {
        return [
            'token' => [
                'required',
                'string',
            ],

            'email' => [
                'required',
                'email:rfc',
                'max:254',
            ],

            'password' => [
                'required',
                'confirmed',
                PasswordRule::defaults(),
            ],
        ];
    }
}
