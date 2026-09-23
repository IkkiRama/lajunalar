<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->unsignedInteger('bank_size')->default(1500)->after('question_count');
            $table->unsignedTinyInteger('number_percentage')->default(50)->after('bank_size');
        });

        DB::table('quizzes')->update(['bank_size' => DB::raw('question_count * 3')]);
        DB::table('quizzes')->where('type', 'number')->update(['number_percentage' => 100]);
        DB::table('quizzes')->where('type', 'letter')->update(['number_percentage' => 0]);

        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->unsignedInteger('number_answered_count')->default(0)->after('average_response_ms');
            $table->unsignedInteger('number_correct_count')->default(0)->after('number_answered_count');
            $table->unsignedInteger('number_average_response_ms')->default(0)->after('number_correct_count');
            $table->unsignedInteger('letter_answered_count')->default(0)->after('number_average_response_ms');
            $table->unsignedInteger('letter_correct_count')->default(0)->after('letter_answered_count');
            $table->unsignedInteger('letter_average_response_ms')->default(0)->after('letter_correct_count');
        });

        DB::table('quiz_attempts')->orderBy('id')->chunkById(100, function ($attempts): void {
            foreach ($attempts as $attempt) {
                $rows = DB::table('quiz_attempt_answers')
                    ->where('quiz_attempt_id', $attempt->id)
                    ->selectRaw('question_type, COUNT(*) as answered_count')
                    ->selectRaw('SUM(CASE WHEN is_correct = 1 THEN 1 ELSE 0 END) as correct_count')
                    ->selectRaw('COALESCE(AVG(response_ms), 0) as average_response_ms')
                    ->groupBy('question_type')
                    ->get()
                    ->keyBy('question_type');

                $number = $rows->get('number');
                $letter = $rows->get('letter');
                DB::table('quiz_attempts')->where('id', $attempt->id)->update([
                    'number_answered_count' => (int) ($number?->answered_count ?? 0),
                    'number_correct_count' => (int) ($number?->correct_count ?? 0),
                    'number_average_response_ms' => (int) round((float) ($number?->average_response_ms ?? 0)),
                    'letter_answered_count' => (int) ($letter?->answered_count ?? 0),
                    'letter_correct_count' => (int) ($letter?->correct_count ?? 0),
                    'letter_average_response_ms' => (int) round((float) ($letter?->average_response_ms ?? 0)),
                ]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->dropColumn([
                'number_answered_count',
                'number_correct_count',
                'number_average_response_ms',
                'letter_answered_count',
                'letter_correct_count',
                'letter_average_response_ms',
            ]);
        });

        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropColumn(['bank_size', 'number_percentage']);
        });
    }
};
