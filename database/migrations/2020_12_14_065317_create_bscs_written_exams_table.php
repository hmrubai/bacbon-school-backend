<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBscsWrittenExamsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bscs_written_exams', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('bscs_exam_id')->unsigned();
            $table->text('question');
            $table->bigInteger('duration')->default(0);
            $table->timestamps();
            $table->foreign('bscs_exam_id')
            ->references('id')
            ->on('bscs_exams')
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
        Schema::dropIfExists('bscs_written_exams');
    }
}
