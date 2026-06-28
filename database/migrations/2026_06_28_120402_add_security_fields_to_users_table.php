<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->ipAddress('last_login_ip')->nullable();
            $table->string('last_login_user_agent')->nullable();
            $table->integer('failed_login_attempts')->default(0);
            $table->timestamp('locked_until')->nullable();
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'email_verified_at',
                'deleted_at',
                'last_login_ip',
                'last_login_user_agent',
                'failed_login_attempts',
                'locked_until'
            ]);
        });
    }
};
