<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ActivityWorkingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'expected_lock_version' => [
                'nullable',
                'integer',
                'min:1',
            ],

            'requested_slug' => [
                'required',
                'string',
                'max:180',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
            ],

            'title' => [
                'required',
                'string',
                'max:200',
            ],

            'excerpt' => [
                'nullable',
                'string',
                'max:500',
            ],

            'category_id' => [
                'nullable',
                'integer',
                'exists:activity_categories,id',
            ],

            'featured_media_id' => [
                'nullable',
                'integer',
                'exists:media,id',
            ],

            'is_featured' => [
                'required',
                'boolean',
            ],

            'seo_title' => [
                'nullable',
                'string',
                'max:200',
            ],

            'seo_description' => [
                'nullable',
                'string',
                'max:500',
            ],

            'og_media_id' => [
                'nullable',
                'integer',
                'exists:media,id',
            ],

            'blocks' => [
                'array',
                'max:100',
            ],

            'blocks.*.block_key' => [
                'nullable',
                'uuid',
            ],

            'blocks.*.type' => [
                'required',
                'string',
            ],

            'blocks.*.data' => [
                'required',
                'array',
            ],

            'related_product_ids' => [
                'array',
                'max:20',
            ],

            'related_product_ids.*' => [
                'integer',
                'distinct',
                'exists:products,id',
            ],
        ];
    }
}
