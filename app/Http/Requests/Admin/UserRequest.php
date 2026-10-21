<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $user = $this->route('user');

        return [
            'name' => [
                'required',
                'string',
                'max:150',
            ],

            'email' => [
                'required',
                'email:rfc',
                'max:254',

                Rule::unique(
                    'users',
                    'email',
                )->ignore(
                    $user?->id,
                ),
            ],

            'lock_version' => [
                'nullable',
                'integer',
                'min:1',
            ],
        ];
    }
}
