<?php

namespace App\Services;

use App\QuizType;
use RuntimeException;

class QuestionGenerator
{
    /**
     * @return array<int, array{position: int, type: string, question_text: string, correct_answer: string, explanation_correct: string, signature: string}>
     */
    public function generate(QuizType $type, int $count, ?int $numberCount = null): array
    {
        $numberTarget = match ($type) {
            QuizType::Number => $count,
            QuizType::Letter => 0,
            QuizType::Mixed => max(0, min($count, $numberCount ?? intdiv($count, 2))),
        };

        $types = array_merge(
            array_fill(0, $numberTarget, QuizType::Number),
            array_fill(0, $count - $numberTarget, QuizType::Letter),
        );
        shuffle($types);

        $questions = [];
        $signatures = [];
        $attempts = 0;

        while (count($questions) < $count && $attempts < ($count * 20)) {
            $attempts++;
            $question = $this->makeQuestion($types[count($questions)]);

            if (isset($signatures[$question['signature']])) {
                continue;
            }

            $signatures[$question['signature']] = true;
            $question['position'] = count($questions) + 1;
            $questions[] = $question;
        }

        if (count($questions) !== $count) {
            throw new RuntimeException('Generator tidak dapat menghasilkan jumlah soal unik yang diminta.');
        }

        return $questions;
    }

    /** @return array{type: string, question_text: string, correct_answer: string, explanation_correct: string, signature: string} */
    private function makeQuestion(QuizType $type): array
    {
        return $type === QuizType::Number
            ? $this->makeMissingNumberQuestion()
            : $this->makeDuplicateLetterQuestion();
    }

    /** @return array{type: string, question_text: string, correct_answer: string, explanation_correct: string, signature: string} */
    private function makeMissingNumberQuestion(): array
    {
        $digits = range(0, 9);
        $missingIndex = random_int(0, 9);
        $answer = (string) $digits[$missingIndex];
        unset($digits[$missingIndex]);
        $digits = array_values($digits);
        shuffle($digits);
        $compactPrompt = implode('', $digits);

        return [
            'type' => QuizType::Number->value,
            'question_text' => implode(' ', $digits),
            'correct_answer' => $answer,
            'explanation_correct' => "Angka yang tidak terdapat dalam susunan adalah {$answer}.",
            'signature' => hash('sha256', QuizType::Number->value.'|'.$compactPrompt),
        ];
    }

    /** @return array{type: string, question_text: string, correct_answer: string, explanation_correct: string, signature: string} */
    private function makeDuplicateLetterQuestion(): array
    {
        $alphabet = range('A', 'Z');
        shuffle($alphabet);
        $letters = array_slice($alphabet, 0, 9);
        $answer = $letters[random_int(0, count($letters) - 1)];
        $letters[] = $answer;
        shuffle($letters);
        $compactPrompt = implode('', $letters);

        return [
            'type' => QuizType::Letter->value,
            'question_text' => implode(' ', $letters),
            'correct_answer' => $answer,
            'explanation_correct' => "Huruf yang muncul dua kali adalah {$answer}.",
            'signature' => hash('sha256', QuizType::Letter->value.'|'.$compactPrompt),
        ];
    }
}
