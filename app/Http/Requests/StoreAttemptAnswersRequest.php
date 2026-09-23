<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreAttemptAnswersRequest extends FormRequest
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
            'answers' => ['required', 'array', 'min:1', 'max:25'],
            'answers.*.position' => ['required', 'integer', 'min:1', 'distinct'],
            'answers.*.question_uuid' => ['required', 'uuid'],
            'answers.*.answer' => ['required', 'string', 'size:1', 'regex:/^[A-Za-z0-9]$/'],
            'answers.*.response_ms' => ['required', 'integer', 'min:0', 'max:300000'],
        ];
    }

    public function messages(): array
    {
        return [
            'answers.max' => 'Maksimal 25 jawaban dapat dikirim dalam satu batch.',
            'answers.*.answer.regex' => 'Jawaban harus berupa satu angka atau huruf.',
        ];
    }
}
