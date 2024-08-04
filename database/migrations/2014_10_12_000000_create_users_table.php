<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('user_code')->nullable();
            $table->string('fcm_id')->nullable();
            $table->string('email')->unique();
            $table->string('password')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('mobile_number');
            $table->string('address');
            $table->bigInteger('current_course_id')->nullable();
            $table->string('image')->nullable();
            $table->string('gender')->nullable();
            $table->string('status')->nullable();
            $table->bigInteger('points')->default(0);
            $table->float('balance', 8, 2)->nullable();
            $table->bigInteger('refference_id')->nullable();
            $table->bigInteger('university_id')->nullable();
            $table->boolean('is_applied_scholarship')->default(false);
            $table->rememberToken();
            $table->timestamps();
        });

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
