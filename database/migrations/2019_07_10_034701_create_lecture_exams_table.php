<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLectureExamsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lecture_exams', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('course_id')->unsigned();
            $table->bigInteger('subject_id')->unsigned();
            $table->bigInteger('chapter_id')->unsigned();
            $table->bigInteger('lecture_id')->unsigned();
            $table->string('exam_name');
            $table->bigInteger('duration')->default(0);
            $table->double('positive_mark')->default(1);
            $table->double('negative_mark')->default(0);
            $table->double('total_mark')->default(0);
            $table->bigInteger('question_number');
            $table->string('status');


            $table->foreign('course_id')
            ->references('id')
            ->on('courses')
            ->onDelete('cascade');

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
        Schema::dropIfExists('lecture_exams');
    }
}
