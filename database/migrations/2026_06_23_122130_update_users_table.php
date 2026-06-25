<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Новые колонки для прогресса
            $table->integer('level')->default(0)->after('points');
            $table->integer('current_streak')->default(0)->after('level');
            $table->integer('max_streak')->default(0)->after('current_streak');
            $table->timestamp('last_activity_at')->nullable()->after('max_streak');
            $table->timestamp('last_login_at')->nullable()->after('last_activity_at');

            // Кэшированные счетчики (для быстрых запросов)
            $table->integer('total_tasks_completed')->default(0)->after('last_login_at');
            $table->integer('total_lessons_completed')->default(0)->after('total_tasks_completed');
            $table->integer('total_pieces_completed')->default(0)->after('total_lessons_completed');
            $table->integer('total_modules_completed')->default(0)->after('total_pieces_completed');

            // Настройки
            $table->jsonb('preferences')->nullable()->after('total_modules_completed');
            $table->jsonb('settings')->nullable()->after('preferences');

            // Статус
            $table->boolean('is_active')->default(true)->after('settings');
            $table->boolean('is_banned')->default(false)->after('is_active');
            $table->timestamp('banned_until')->nullable()->after('is_banned');
            $table->text('ban_reason')->nullable()->after('banned_until');

            // Индексы
            $table->index('level');
            $table->index('points');
            $table->index('current_streak');
            $table->index('last_activity_at');
            $table->index('is_active');
            $table->index('user_type');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'level',
                'current_streak',
                'max_streak',
                'last_activity_at',
                'last_login_at',
                'total_tasks_completed',
                'total_lessons_completed',
                'total_pieces_completed',
                'total_modules_completed',
                'preferences',
                'settings',
                'is_active',
                'is_banned',
                'banned_until',
                'ban_reason',
            ]);
        });
    }
};
