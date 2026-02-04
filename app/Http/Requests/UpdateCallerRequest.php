<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCallerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Handled in controller gate
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:45',
            'cpr' => 'required|string|max:255|unique:callers,cpr,'.$this->route('caller')->id,
            'caller_type' => 'required|string|in:family,individual',
            'hits' => 'integer|min:0',
            'is_winner' => 'boolean',
            'notes' => 'nullable|string',
        ];
    }
}
