<?php

namespace App\Http\Requests\Admin;

use App\Enums\ECatalogAvailability;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EcatalogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $entry = $this->route(
            'ecatalog',
        );

        return [
            'expected_lock_version' => [
                'nullable',
                'integer',
                'min:1',
            ],

            'product_id' => [
                'required',
                'integer',
                'exists:products,id',
            ],

            'catalog_code' => [
                'required',
                'string',
                'max:120',
                Rule::unique(
                    'ecatalog_entries',
                    'catalog_code',
                )->ignore(
                    $entry?->id,
                ),
            ],

            'procurement_title' => [
                'required',
                'string',
                'max:200',
            ],

            'procurement_category' => [
                'nullable',
                'string',
                'max:150',
            ],

            'notes' => [
                'nullable',
                'string',
            ],

            'unit_package' => [
                'nullable',
                'string',
                'max:150',
            ],

            'availability' => [
                'required',
                Rule::enum(
                    ECatalogAvailability::class,
                ),
            ],

            'reference_url' => [
                'nullable',
                'url:http,https',
                'max:2048',
            ],

            'effective_at' => [
                'nullable',
                'date',
            ],

            'expires_at' => [
                'nullable',
                'date',
                'after:effective_at',
            ],

            'sort_order' => [
                'required',
                'integer',
                'min:0',
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
        ];
    }
}
