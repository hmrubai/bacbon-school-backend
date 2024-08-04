<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBscsExamsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bscs_exams', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('exam_name');
            $table->string('exam_name_bn');
            $table->bigInteger('duration')->default(0);
            $table->double('positive_mark')->default(1);
            $table->double('negative_mark')->default(0);
            $table->double('total_mark')->default(0);
            $table->bigInteger('question_number');
            $table->string('status');
            $table->integer('sequence')->default(0);
            $table->datetime('appeared_from');
            $table->datetime('appeared_to');
            $table->boolean('is_active')->default(true);
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
        Schema::dropIfExists('bscs_exams');
    }
}
