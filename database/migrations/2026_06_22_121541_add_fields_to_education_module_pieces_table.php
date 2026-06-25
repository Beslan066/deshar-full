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
        Schema::table('education_module_pieces', function (Blueprint $table) {
            // Добавляем education_module_id
            if (!Schema::hasColumn('education_module_pieces', 'education_module_id')) {
                $table->foreignId('education_module_id')
                    ->after('fon')
                    ->constrained('education_modules')
                    ->cascadeOnDelete();
            }

            // Добавляем is_required
            if (!Schema::hasColumn('education_module_pieces', 'is_required')) {
                $table->boolean('is_required')->default(true)->after('is_published');
            }

            // Добавляем xp_reward
            if (!Schema::hasColumn('education_module_pieces', 'xp_reward')) {
                $table->integer('xp_reward')->default(10)->after('sort_order');
            }

            // Добавляем estimated_time
            if (!Schema::hasColumn('education_module_pieces', 'estimated_time')) {
                $table->integer('estimated_time')->nullable()->after('xp_reward');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('education_module_pieces', function (Blueprint $table) {
            $columns = ['education_module_id', 'is_required', 'xp_reward', 'estimated_time'];

            foreach ($columns as $column) {
                if (Schema::hasColumn('education_module_pieces', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
