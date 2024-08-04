<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCareersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('careers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_id')->unsigned();
            $table->bigInteger('university_id')->unsigned()->nullable();
            $table->string('name', 191);
            $table->string('phone', 20);
            $table->string('email', 20);
            $table->string('university_name')->nullable();
            $table->string('cover_letter')->nullable();
            $table->float('work_experience')->default(0);
            $table->string('work_experience_duration_type')->nullable();
            $table->integer('expected_salary')->default(0);

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
        Schema::dropIfExists('careers');
    }
}