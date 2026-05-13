<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Добавляем недостающие поля
            $table->string('avatar')->nullable()->after('email');
            $table->foreignId('country_id')->nullable()->after('remember_token');
            $table->foreignId('region_id')->nullable()->after('country_id');
            $table->foreignId('district_id')->nullable()->after('region_id');
            $table->foreignId('city_id')->nullable()->after('district_id');
            $table->foreignId('school_id')->nullable()->after('city_id');
            // school_class_id уже есть, проверяем
            if (!Schema::hasColumn('users', 'school_class_id')) {
                $table->foreignId('school_class_id')->nullable()->after('school_id');
            }
            // role_id уже есть, проверяем
            if (!Schema::hasColumn('users', 'role_id')) {
                $table->foreignId('role_id')->nullable()->after('school_class_id');
            }
            $table->integer('points')->default(0)->after('role_id');
            $table->date('birth_date')->nullable()->after('points');
            $table->string('user_type')->nullable()->default('ing')->after('birth_date');

            // Добавляем внешние ключи
            $table->foreign('country_id')->references('id')->on('countries')->onDelete('set null');
            $table->foreign('region_id')->references('id')->on('regions')->onDelete('set null');
            $table->foreign('district_id')->references('id')->on('districts')->onDelete('set null');
            $table->foreign('city_id')->references('id')->on('cities')->onDelete('set null');
            $table->foreign('school_id')->references('id')->on('schools')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Удаляем внешние ключи
            $table->dropForeign(['country_id']);
            $table->dropForeign(['region_id']);
            $table->dropForeign(['district_id']);
            $table->dropForeign(['city_id']);
            $table->dropForeign(['school_id']);
            $table->dropForeign(['school_class_id']);
            $table->dropForeign(['role_id']);

            // Удаляем поля
            $table->dropColumn([
                'avatar',
                'country_id',
                'region_id',
                'district_id',
                'city_id',
                'school_id',
                'points',
                'birth_date',
                'user_type'
            ]);

            // school_class_id и role_id не удаляем, так как они уже были
        });
    }
};
