<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLectureExamQuestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lecture_exam_questions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('subject_id')->unsigned();
            $table->bigInteger('chapter_id')->unsigned();
            $table->bigInteger('lecture_id')->unsigned();
            $table->bigInteger('exam_id')->unsigned();
            $table->bigInteger('question_id')->unsigned();
            $table->string('status')->nullable();

            $table->foreign('subject_id')
            ->references('id')
            ->on('subjects')
            ->onDelete('cascade');

            $table->foreign('chapter_id')
            ->references('id')
            ->on('chapters')
            ->onDelete('cascade');

            $table->foreign('lecture_id')
            ->references('id')
            ->on('lecture_videos')
            ->onDelete('cascade');



            $table->foreign('exam_id')
            ->references('id')
            ->on('lecture_exams')
            ->onDelete('cascade');


            $table->foreign('question_id')
            ->references('id')
            ->on('lecture_questions')
            ->onDelete('cascade');


            $table->charset = 'utf8';
            $table->collation = 'utf8_unicode_ci';

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
        Schema::dropIfExists('lecture_exam_questions');
    }
}
