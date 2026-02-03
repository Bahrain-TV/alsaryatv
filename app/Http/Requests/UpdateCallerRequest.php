<?php

namespace App\Http\Requests;

use App\Rules\SanitizedInput;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCallerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // This will be checked via the Gate in the controller
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'caller_id' => 'required|exists:callers,id',
            'call_date' => 'required|date',
            'call_duration' => 'required|integer|min:1',
            'call_status' => 'required|in:answered,missed,busy',
            'notes' => ['sometimes', 'nullable', 'string', 'max:1000', new SanitizedInput],
        ];
    }
}
