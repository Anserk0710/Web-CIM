<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class MediaUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission(
            'media.upload',
        ) ?? false;
    }

    public function rules(): array
    {
        return [
            'file' => [
                'required',
                File::types([
                    'jpg',
                    'jpeg',
                    'png',
                    'webp',
                    'pdf',
                    'mp4',
                ])->max('10mb'),
                'extensions:jpg,jpeg,png,webp,pdf,mp4',
            ],

            'default_alt' => [
                'nullable',
                'string',
                'max:500',
            ],

            'caption' => [
                'nullable',
                'string',
            ],
        ];
    }
}
