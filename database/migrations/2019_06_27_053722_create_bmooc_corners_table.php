<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBmoocCornersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bmooc_corners', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('title');
            $table->longText('description');
            $table->text('url');
            $table->text('thumbnail');
            $table->integer('duration');
            $table->string('status');
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
        Schema::dropIfExists('bmooc_corners');
    }
}
