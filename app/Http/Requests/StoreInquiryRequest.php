<?php

namespace App\Http\Requests;

use App\Models\Inquiry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInquiryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('phone')) {
            $this->merge([
                'phone' => preg_replace('/[\s\-()]+/', '', (string) $this->input('phone')),
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'regex:/^(\+?234|0)[789]\d{9}$/'],
            'subject' => ['required', 'string', Rule::in(array_keys(Inquiry::SUBJECTS))],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
            'related_url' => ['nullable', 'string', 'max:500'],
            'vehicle_id' => ['nullable', 'prohibited_unless:subject,automobiles', 'integer', Rule::exists('vehicles', 'id')->where('is_published', true)->where('status', 'available')],
            'inquiry_category' => ['nullable', Rule::in(['general', 'negotiation', 'availability', 'documents', 'inspection', 'other'])],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'phone.regex' => 'Enter a valid Nigerian phone number.',
            'message.min' => 'Please share a few more details so we can help.',
        ];
    }
}
