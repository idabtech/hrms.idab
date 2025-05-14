<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table){
            $table->bigIncrements('id');
            $table->string('name');
            $table->bigInteger('mobile');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('otp');
            $table->string('is_verified');
            $table->string('type');
            $table->string('avatar')->nullable();
            $table->string('lang');
            $table->integer('plan')->nullable();
            $table->date('plan_expire_date')->nullable();
            $table->timestamp('last_login')->nullable();
            $table->integer('is_active')->default('1');
            $table->integer('term_and_condition')->default('0');
            $table->string('token');
            $table->string('created_by');
            $table->rememberToken();
            $table->timestamps();
        }
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
