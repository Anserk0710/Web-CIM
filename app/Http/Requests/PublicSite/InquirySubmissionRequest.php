<?php

// path: app/Http/Requests/PublicSite/InquirySubmissionRequest.php

namespace App\Http\Requests\PublicSite;

use App\Enums\InquiryType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InquirySubmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => [
                'required',
                Rule::enum(InquiryType::class),
            ],

            'name' => [
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

            'organization' => [
                'nullable',
                'string',
                'max:200',
            ],

            'message' => [
                'required',
                'string',
                'max:5000',
            ],

            'product_id' => [
                'nullable',
                'integer',
                'required_if:type,PRODUCT,ECATALOG',
                'prohibited_unless:type,PRODUCT,ECATALOG',
                'exists:products,id',
            ],

            'ecatalog_entry_id' => [
                'nullable',
                'integer',
                'required_if:type,ECATALOG',
                'prohibited_unless:type,ECATALOG',
                'exists:ecatalog_entries,id',
            ],

            'privacy_notice_version' => [
                'required',
                'string',
                'max:40',
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
            'product_id.required_if' => 'Pilih produk yang terkait dengan inquiry.',

            'ecatalog_entry_id.required_if' => 'Pilih E-Catalog yang terkait dengan inquiry.',

            'privacy_notice_version.required' => 'Privacy notice tidak valid. Muat ulang halaman.',

            'website.max' => 'Permintaan tidak dapat diproses.',
        ];
    }
}
