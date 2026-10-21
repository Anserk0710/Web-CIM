<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class MediaMetadataRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()
            ?->hasPermission(
                'media.update',
            ) ?? false;
    }

    public function rules(): array
    {
        return [
            'default_alt' => [
                'nullable',
                'string',
                'max:500',
            ],

            'caption' => [
                'nullable',
                'string',
            ],

            'lock_version' => [
                'required',
                'integer',
                'min:1',
            ],
        ];
    }
}
