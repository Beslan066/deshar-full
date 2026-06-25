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
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->foreignId('piece_id')->constrained('education_module_pieces')->cascadeOnDelete();
            $table->text('description')->nullable();
            $table->boolean('is_published')->default(false);
            $table->boolean('is_required')->default(true);
            $table->integer('sort_order')->default(0);
            $table->integer('xp_reward')->default(10);
            $table->integer('estimated_time')->nullable();
            $table->jsonb('metadata')->nullable();
            $table->timestamps();

            $table->index(['piece_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};
