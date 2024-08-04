<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChapterExamQuestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chapter_exam_questions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('subject_id')->unsigned();
            $table->bigInteger('chapter_id')->unsigned();
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

            $table->foreign('exam_id')
            ->references('id')
            ->on('chapter_exams')
            ->onDelete('cascade');


            $table->foreign('question_id')
            ->references('id')
            ->on('chapter_questions')
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
        Schema::dropIfExists('chapter_exam_questions');
    }
}
