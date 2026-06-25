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
        Schema::create('task_types', function (Blueprint $table) {
            $table->id();

            // 🔥 Основные поля
            $table->string('slug', 50)->unique()->comment('Уникальный идентификатор типа');
            $table->string('name', 100)->comment('Название типа задания');
            $table->string('icon', 50)->nullable()->comment('CSS класс иконки');
            $table->text('description')->nullable()->comment('Описание типа');

            // ⭐ JSONB поле для дефолтного конфига
            $table->jsonb('default_config')->nullable()->comment('Дефолтная структура JSONB конфига');

            // 📊 Статусы и сортировка
            $table->boolean('is_active')->default(true)->comment('Активен/неактивен');
            $table->integer('sort_order')->default(0)->comment('Порядок сортировки');

            $table->timestamps();

            // 🚀 Индексы
            $table->index('is_active');
            $table->index('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_types');
    }
};
