<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReviewExamDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('review_exam_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('review_exam_id')->unsigned();
            $table->bigInteger('lecture_exam_id')->unsigned();
            $table->timestamps();


            $table->foreign('review_exam_id')
            ->references('id')
            ->on('review_exams')
            ->onDelete('cascade');

            $table->foreign('lecture_exam_id')
            ->references('id')
            ->on('lecture_exams')
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
        Schema::dropIfExists('review_exam_details');
    }
}
