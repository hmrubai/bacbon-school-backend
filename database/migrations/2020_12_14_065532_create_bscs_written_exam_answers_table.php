<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBscsWrittenExamAnswersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bscs_written_exam_answers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_id')->unsigned();
            $table->bigInteger('bscs_written_exam_id')->unsigned();
            $table->integer('answer');
            $table->datetime('start_time');
            $table->datetime('end_time');
            $table->string('status');
            $table->timestamps();

            $table->foreign('bscs_written_exam_id')
            ->references('id')
            ->on('bscs_written_exams')
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
        Schema::dropIfExists('bscs_written_exam_answers');
    }
}
