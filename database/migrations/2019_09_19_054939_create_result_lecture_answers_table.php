<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateResultLectureAnswersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('result_lecture_answers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('question_id')->unsigned();
            $table->bigInteger('user_id')->unsigned();
            $table->bigInteger('result_lecture_id')->unsigned();
            $table->integer('answer');
            $table->timestamps();

            $table->foreign('result_lecture_id')
            ->references('id')
            ->on('result_lectures')
            ->onDelete('cascade');

            $table->foreign('question_id')
            ->references('id')
            ->on('lecture_questions')
            ->onDelete('cascade');

            $table->foreign('user_id')
            ->references('id')
            ->on('users')
            ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('result_lecture_answers');
    }
}
