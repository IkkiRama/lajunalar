<?php

namespace Database\Seeders;

use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\User;
use App\QuizStatus;
use App\QuizType;
use App\Services\QuestionGenerator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $adminEmail = config('lajunalar.admin_emails')[0] ?? 'admin@lajunalar.test';
        $admin = User::query()->updateOrCreate(
            ['email' => $adminEmail],
            ['name' => 'Administrator LajuNalar', 'email_verified_at' => now(), 'role' => 'admin', 'password' => null],
        );

        $packages = [
            ['slug' => 'simulasi-campuran-1', 'title' => 'Simulasi Campuran 1', 'type' => QuizType::Mixed, 'number_count' => 250, 'description' => 'Latihan seimbang: angka hilang dan huruf ganda dalam satu sesi cepat.'],
            ['slug' => 'fokus-angka-1', 'title' => 'Fokus Angka 1', 'type' => QuizType::Number, 'number_count' => 500, 'description' => 'Asah kecepatan menemukan satu angka yang tidak muncul dalam susunan.'],
            ['slug' => 'fokus-huruf-1', 'title' => 'Fokus Huruf 1', 'type' => QuizType::Letter, 'number_count' => 0, 'description' => 'Temukan satu huruf yang muncul dua kali dengan cepat dan akurat.'],
        ];

        foreach ($packages as $definition) {
            $quiz = Quiz::query()->updateOrCreate(['slug' => $definition['slug']], [
                'title' => $definition['title'],
                'description' => $definition['description'],
                'type' => $definition['type'],
                'status' => QuizStatus::Published,
                'question_count' => 500,
                'duration_seconds' => 300,
                'passing_score' => null,
                'generation_config' => ['number_count' => $definition['number_count']],
                'published_at' => now(),
                'created_by' => $admin->id,
            ]);

            if ($quiz->questions()->count() === 500) {
                continue;
            }

            $quiz->questions()->delete();
            $questions = app(QuestionGenerator::class)->generate($definition['type'], 500, $definition['number_count']);
            $timestamp = now();
            foreach (array_chunk($questions, 100) as $chunk) {
                QuizQuestion::query()->insert(array_map(fn (array $question): array => [
                    'uuid' => (string) Str::uuid(),
                    'quiz_id' => $quiz->id,
                    ...$question,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ], $chunk));
            }
        }
    }
}
