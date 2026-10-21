<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class OfficeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()
            ?->hasPermission(
                'settings.update',
            ) ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:150',
            ],

            'address' => [
                'required',
                'string',
            ],

            'phone' => [
                'nullable',
                'string',
                'max:32',
            ],

            'email' => [
                'nullable',
                'email',
                'max:254',
            ],

            'latitude' => [
                'nullable',
                'numeric',
                'between:-90,90',
                'required_with:longitude',
            ],

            'longitude' => [
                'nullable',
                'numeric',
                'between:-180,180',
                'required_with:latitude',
            ],

            'hours' => [
                'nullable',
                'array',
            ],

            'is_head_office' => [
                'required',
                'boolean',
            ],

            'sort_order' => [
                'required',
                'integer',
                'min:0',
            ],

            'is_active' => [
                'required',
                'boolean',
            ],

            'lock_version' => [
                'nullable',
                'integer',
                'min:1',
            ],
        ];
    }
}
