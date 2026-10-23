<?php

namespace App\Http\Requests\PublicSite;

use Illuminate\Foundation\Http\FormRequest;

class CareerApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'full_name' => [
                'required',
                'string',
                'max:150',
            ],

            'email' => [
                'required',
                'email:rfc',
                'max:254',
            ],

            'phone' => [
                'nullable',
                'string',
                'max:32',
            ],

            'introduction' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'portfolio_url' => [
                'nullable',
                'url:http,https',
                'max:2048',
            ],

            'linkedin_url' => [
                'nullable',
                'url:http,https',
                'max:2048',
            ],

            'cv' => [
                'required',
                'file',
                'mimes:pdf',
                'mimetypes:application/pdf,application/x-pdf',
                'max:5120',
            ],

            'consent_version_id' => [
                'required',
                'integer',
                'exists:consent_versions,id',
            ],

            'consent' => [
                'accepted',
            ],

            'website' => [
                'nullable',
                'string',
                'max:0',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'cv.max' => 'CV maksimal 5 MiB.',
            'cv.mimes' => 'CV harus berupa file PDF.',
            'cv.mimetypes' => 'CV harus berupa file PDF yang valid.',
            'consent.accepted' => 'Persetujuan pemrosesan data wajib diberikan.',
            'website.max' => 'Permintaan tidak dapat diproses.',
        ];
    }
}
