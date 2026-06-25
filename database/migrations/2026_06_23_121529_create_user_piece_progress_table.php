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
        Schema::create('user_piece_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('piece_id')->constrained('education_module_pieces')->cascadeOnDelete();

            $table->enum('status', [
                'not_started',
                'in_progress',
                'completed',
                'failed'
            ])->default('not_started');

            $table->float('progress_percentage')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('last_activity_at')->nullable();
            $table->integer('time_spent_seconds')->default(0);
            $table->jsonb('metadata')->nullable();

            $table->timestamps();

            // Индексы
            $table->unique(['user_id', 'piece_id']);
            $table->index(['user_id', 'status']);
            $table->index(['piece_id', 'status']);
            $table->index('progress_percentage');
            $table->index('last_activity_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_piece_progress');
    }
};
