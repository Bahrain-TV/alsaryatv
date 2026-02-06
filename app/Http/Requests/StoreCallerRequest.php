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
            'cpr' => 'required|string|max:255',
            'phone_number' => 'required|string|max:45',
            'registration_type' => 'required|string|in:family,individual',
            'family_name' => 'nullable|string|max:255',
            'family_members' => 'nullable|integer|min:2|max:10',
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
            'registration_type.required' => 'نوع التسجيل مطلوب',
            'registration_type.in' => 'نوع التسجيل يجب أن يكون عائلة أو فرد',
            'family_members.min' => 'عدد أفراد العائلة يجب أن يكون على الأقل 2',
            'family_members.max' => 'عدد أفراد العائلة يجب أن لا يتجاوز 10',
        ];
    }
}
