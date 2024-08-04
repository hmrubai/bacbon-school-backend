<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLectureVideosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lecture_videos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('title');
            $table->text('title_bn');
            $table->longText('description');
            $table->text('url');
            $table->text('full_url');
            $table->text('thumbnail');
            $table->integer('duration');
            $table->bigInteger('chapter_id')->unsigned();
            $table->bigInteger('course_id')->unsigned();
            $table->bigInteger('subject_id')->unsigned();
            $table->boolean('isFree');
            $table->string('code', 30);
            $table->float('price', 8, 2);
            $table->string('status');
            $table->float('rating', 8, 2)->default(0);
            $table->timestamps();

            $table->foreign('chapter_id')
            ->references('id')
            ->on('chapters')
            ->onDelete('cascade');

            $table->foreign('course_id')
            ->references('id')
            ->on('courses')
            ->onDelete('cascade');

            $table->foreign('subject_id')
            ->references('id')
            ->on('subjects')
            ->onDelete('cascade');

            $table->charset = 'utf8';
            $table->collation = 'utf8_unicode_ci';

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lecture_videos');
    }
}
