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
        Schema::create('school_classses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('school_id')->constrained();

            $table->foreignId('teacher_id')
                ->nullable() // У первого начальника нет руководителя
                ->after('id') // Опционально: разместить после id
                ->constrained('users') // Ссылка на таблицу users
                ->onDelete('set null'); // Если удален начальник, поле станет NULL

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_classses');
    }
};
