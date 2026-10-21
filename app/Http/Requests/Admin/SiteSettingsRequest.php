<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SiteSettingsRequest extends FormRequest
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
            'company.legal_name' => [
                'required',
                'string',
                'max:200',
            ],

            'company.display_name' => [
                'required',
                'string',
                'max:200',
            ],

            'company.about' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'contact.email' => [
                'nullable',
                'email',
                'max:254',
            ],

            'contact.phone' => [
                'nullable',
                'string',
                'max:32',
            ],

            'contact.whatsapp' => [
                'nullable',
                'string',
                'max:32',
            ],

            'coverage.regions' => [
                'array',
                'max:100',
            ],

            'coverage.regions.*' => [
                'string',
                'max:120',
            ],

            'seo_defaults.title_suffix' => [
                'nullable',
                'string',
                'max:200',
            ],

            'seo_defaults.default_description' => [
                'nullable',
                'string',
                'max:500',
            ],

            'branding.logo_media_id' => [
                'nullable',
                'integer',
                'exists:media,id',
            ],

            'branding.favicon_media_id' => [
                'nullable',
                'integer',
                'exists:media,id',
            ],

            'footer.tagline' => [
                'nullable',
                'string',
                'max:500',
            ],

            'footer.copyright' => [
                'nullable',
                'string',
                'max:500',
            ],
        ];
    }
}
