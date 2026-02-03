<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCallerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Since this endpoint is public (noted in the controller), we'll allow all requests
        // Authorization is handled separately in the controller using policies
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'cpr' => 'required|string|max:255',  // Allow both numeric and alphanumeric CPR values
            'phone_number' => 'required|string|max:45',
            'caller_type' => 'required|string|in:family,individual',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'الاسم مطلوب',
            'cpr.required' => 'رقم الهوية مطلوب',
            'phone_number.required' => 'رقم الهاتف مطلوب',
            'caller_type.required' => 'نوع المتصل مطلوب',
            'caller_type.in' => 'نوع المتصل يجب أن يكون عائلة أو فرد',
        ];
    }
}
