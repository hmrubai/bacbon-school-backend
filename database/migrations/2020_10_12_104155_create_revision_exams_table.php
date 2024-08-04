<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRevisionExamsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('revision_exams', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('course_id')->unsigned();
            $table->string('exam_name');
            $table->string('exam_name_bn');
            $table->bigInteger('duration')->default(0);
            $table->double('positive_mark')->default(1);
            $table->double('negative_mark')->default(0);
            $table->double('total_mark')->default(0);
            $table->bigInteger('question_number');
            $table->integer('question_number_per_subject')->nullable();
            $table->string('status')->nullable();
            $table->integer('week_number')->nullable();

            $table->foreign('course_id')
            ->references('id')
            ->on('courses')
            ->onDelete('cascade');

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
        Schema::dropIfExists('revision_exams');
    }
}
