<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SyncUserRolesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'role_ids' => [
                'required',
                'array',
            ],

            'role_ids.*' => [
                'integer',
                'distinct',
            ],

            'lock_version' => [
                'required',
                'integer',
                'min:1',
            ],
        ];
    }
}
