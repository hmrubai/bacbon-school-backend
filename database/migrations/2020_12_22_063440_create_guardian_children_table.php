<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGuardianChildrenTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('guardian_children', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('guardian_id');
            $table->bigInteger('user_id');
            $table->string('relation')->nullable();
            $table->boolean('is_accepted_by_student')->default(0);
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
        Schema::dropIfExists('guardian_children');
    }
}
