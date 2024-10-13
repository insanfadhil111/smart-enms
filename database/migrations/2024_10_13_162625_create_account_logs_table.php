<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccountLogsTable extends Migration
{
    public function up()
    {
        Schema::create('account_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamp('login_time');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('account_logs');
    }
}

