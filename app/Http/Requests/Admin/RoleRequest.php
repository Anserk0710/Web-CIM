<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $role = $this->route('role');

        return [
            'name' => [
                'required',
                'string',
                'max:80',

                Rule::unique(
                    'roles',
                    'name',
                )->ignore(
                    $role?->id,
                ),
            ],

            'description' => [
                'nullable',
                'string',
                'max:255',
            ],

            'lock_version' => [
                'nullable',
                'integer',
                'min:1',
            ],
        ];
    }
}
