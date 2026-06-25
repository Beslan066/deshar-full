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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_id')->constrained()->cascadeOnDelete(); // ← changed!
            $table->foreignId('task_type_id')->constrained();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->jsonb('config')->default('{}');
            $table->jsonb('hints')->default('[]');
            $table->integer('max_attempts')->default(3);
            $table->integer('time_limit_seconds')->default(0);
            $table->integer('xp_reward')->default(10);
            $table->boolean('is_published')->default(false);
            $table->boolean('is_required')->default(true);
            $table->timestamps();

            $table->index(['lesson_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
