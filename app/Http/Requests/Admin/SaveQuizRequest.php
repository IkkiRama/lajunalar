<?php

namespace App\Http\Requests\Admin;

use App\QuizStatus;
use App\QuizType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveQuizRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:5000'],
            'type' => ['required', Rule::enum(QuizType::class)],
            'status' => ['required', Rule::enum(QuizStatus::class)],
            'question_count' => ['required', 'integer', 'min:1', 'max:2000'],
            'bank_size' => ['required', 'integer', 'min:1', 'max:5000', 'gte:question_count'],
            'number_percentage' => ['required', 'integer', 'min:0', 'max:100'],
            'duration_minutes' => ['required', 'numeric', 'min:1', 'max:180'],
            'passing_score' => ['nullable', 'integer', 'min:0', 'max:100'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after:starts_at'],
        ];
    }
}
