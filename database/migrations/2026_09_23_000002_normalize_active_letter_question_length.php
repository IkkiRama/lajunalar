<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('quiz_questions')
            ->where('type', 'letter')
            ->whereNull('deleted_at')
            ->orderBy('id')
            ->chunkById(500, function ($questions): void {
                foreach ($questions as $question) {
                    $letters = preg_split('/\s+/', trim($question->question_text)) ?: [];

                    if (count($letters) !== 11) {
                        continue;
                    }

                    $answer = mb_strtoupper($question->correct_answer);
                    $counts = array_count_values(array_map('mb_strtoupper', $letters));

                    if (($counts[$answer] ?? 0) !== 2) {
                        continue;
                    }

                    for ($index = count($letters) - 1; $index >= 0; $index--) {
                        if (mb_strtoupper($letters[$index]) === $answer) {
                            continue;
                        }

                        array_splice($letters, $index, 1);
                        break;
                    }

                    $questionText = implode(' ', $letters);
                    DB::table('quiz_questions')->where('id', $question->id)->update([
                        'question_text' => $questionText,
                        'signature' => hash('sha256', 'letter|'.str_replace(' ', '', $questionText)),
                        'updated_at' => now(),
                    ]);
                }
            });
    }

    public function down(): void
    {
        // Koreksi data ini tidak dapat dibalik karena huruf yang dibuang tidak disimpan.
    }
};
