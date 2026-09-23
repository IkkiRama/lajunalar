<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreQuestionReportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'min:10', 'max:1000'],
            'attempt_uuid' => ['nullable', 'uuid', 'exists:quiz_attempts,uuid'],
        ];
    }

    public function messages(): array
    {
        return [
            'reason.min' => 'Jelaskan masalah soal minimal 10 karakter.',
        ];
    }
}
