<?php

namespace App\Http\Requests\Admin;

use App\Enums\EmploymentType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CareerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $career = $this->route(
            'career',
        );

        return [
            'expected_lock_version' => [
                'nullable',
                'integer',
                'min:1',
            ],

            'slug' => [
                'required',
                'string',
                'max:180',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',

                Rule::unique(
                    'careers',
                    'slug',
                )->ignore(
                    $career?->id,
                ),
            ],

            'title' => [
                'required',
                'string',
                'max:200',
            ],

            'department' => [
                'required',
                'string',
                'max:120',
            ],

            'location' => [
                'required',
                'string',
                'max:150',
            ],

            'employment_type' => [
                'required',
                Rule::enum(
                    EmploymentType::class,
                ),
            ],

            'summary' => [
                'nullable',
                'string',
            ],

            'responsibilities' => [
                'required',
                'string',
            ],

            'requirements' => [
                'required',
                'string',
            ],

            'opens_at_local' => [
                'nullable',
                'date_format:Y-m-d\TH:i',
            ],

            'closes_at_local' => [
                'nullable',
                'date_format:Y-m-d\TH:i',
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
