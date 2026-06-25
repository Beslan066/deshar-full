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
        Schema::create('user_task_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->string('status', 20)->default('pending');
            $table->integer('attempts_count')->default(0);
            $table->integer('score')->default(0);
            $table->integer('max_score')->nullable();
            $table->jsonb('answer_history')->default('[]');
            $table->jsonb('user_answers')->default('[]');
            $table->integer('time_spent_seconds')->default(0);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'task_id']);
            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_task_progress');
    }
};
