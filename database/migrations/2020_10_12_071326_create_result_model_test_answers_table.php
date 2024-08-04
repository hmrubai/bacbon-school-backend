<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateResultModelTestAnswersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('result_model_test_answers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('question_id')->unsigned();
            $table->bigInteger('user_id')->unsigned();
            $table->bigInteger('result_model_test_id')->unsigned();
            $table->integer('answer');
            $table->timestamps();

            $table->foreign('result_model_test_id')
            ->references('id')
            ->on('result_model_tests')
            ->onDelete('cascade');

            $table->foreign('question_id')
            ->references('id')
            ->on('model_test_questions')
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
        Schema::dropIfExists('result_model_test_answers');
    }
}
