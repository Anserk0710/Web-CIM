<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ProductWorkingRequest extends FormRequest
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

            'name' => [
                'required',
                'string',
                'max:200',
            ],

            'summary' => [
                'nullable',
                'string',
                'max:500',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'category_id' => [
                'required',
                'integer',
                'exists:product_categories,id',
            ],

            'brand_id' => [
                'nullable',
                'integer',
                'exists:brands,id',
            ],

            'type_id' => [
                'nullable',
                'integer',
                'exists:product_types,id',
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

            'gallery' => [
                'array',
                'max:12',
            ],

            'gallery.*.media_id' => [
                'required',
                'integer',
                'distinct',
                'exists:media,id',
            ],

            'gallery.*.alt_text' => [
                'nullable',
                'string',
                'max:500',
            ],

            'gallery.*.caption' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'brochure_media_id' => [
                'nullable',
                'integer',
                'exists:media,id',
            ],

            'highlights' => [
                'array',
                'max:20',
            ],

            'highlights.*' => [
                'nullable',
                'string',
                'max:500',
            ],

            'spec_groups' => [
                'array',
                'max:20',
            ],

            'spec_groups.*.name' => [
                'required',
                'string',
                'max:150',
            ],

            'spec_groups.*.items' => [
                'array',
                'max:50',
            ],

            'spec_groups.*.items.*.label' => [
                'required',
                'string',
                'max:150',
            ],

            'spec_groups.*.items.*.value' => [
                'required',
                'string',
            ],

            'spec_groups.*.items.*.unit' => [
                'nullable',
                'string',
                'max:50',
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
