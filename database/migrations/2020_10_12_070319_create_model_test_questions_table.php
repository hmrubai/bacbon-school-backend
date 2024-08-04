<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateModelTestQuestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('model_test_questions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('model_test_id')->unsigned();
            $table->bigInteger('subject_id')->unsigned();
            $table->text('question');
            $table->text('option1');
            $table->text('option2');
            $table->text('option3');
            $table->text('option4');
            $table->integer('correct_answer');
            $table->string('explanation')->nullable();
            $table->longText('explanation_text')->nullable();
            $table->string('status');

            $table->foreign('model_test_id')
            ->references('id')
            ->on('model_tests')
            ->onDelete('cascade');

            $table->foreign('subject_id')
            ->references('id')
            ->on('subjects')
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
        Schema::dropIfExists('model_test_questions');
    }
}
