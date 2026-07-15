<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('user_task_progress', function (Blueprint $table) {
            // Добавляем недостающие поля
            $table->timestamp('started_at')->nullable()->after('status');
            $table->json('last_answer')->nullable()->after('user_answers');
            $table->timestamp('last_activity_at')->nullable()->after('last_answer');
            $table->float('progress_percentage')->default(0)->after('time_spent_seconds');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_task_progress', function (Blueprint $table) {
            $table->dropColumn(['started_at', 'last_answer', 'last_activity_at', 'progress_percentage']);
        });
    }
};
