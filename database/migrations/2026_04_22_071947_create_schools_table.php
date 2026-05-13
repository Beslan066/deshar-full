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
        Schema::create('schools', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('country_id')->constrained();
            $table->foreignId('city_id')->nullable()->constrained();
            $table->foreignId('region_id')->constrained();
            $table->foreignId('district_id')->constrained();


            $table->foreignId('director_id')
                ->nullable() // У первого начальника нет руководителя
                ->after('id') // Опционально: разместить после id
                ->constrained('users') // Ссылка на таблицу users
                ->onDelete('set null'); // Если удален начальник, поле станет NULL

            $table->foreignId('manager_id')
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
        Schema::dropIfExists('schools');
    }
};
