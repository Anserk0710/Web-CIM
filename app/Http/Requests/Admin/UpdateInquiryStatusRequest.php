<?php

// path: app/Http/Requests/Admin/UpdateInquiryStatusRequest.php

namespace App\Http\Requests\Admin;

use App\Enums\InquiryStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateInquiryStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'target_status' => [
                'required',
                Rule::enum(InquiryStatus::class),
            ],

            'reason' => [
                'nullable',
                'string',
                'max:2000',
            ],

            'lock_version' => [
                'required',
                'integer',
                'min:1',
            ],
        ];
    }
}
