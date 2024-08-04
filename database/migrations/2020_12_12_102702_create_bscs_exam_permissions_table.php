<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBscsExamPermissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bscs_exam_permissions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('bscs_exam_id')->unsigned();
            $table->bigInteger('user_id')->unsigned();
            $table->integer('mcq_permission_count')->default(0);
            $table->integer('written_permission_count')->default(0);
            $table->timestamps();

            $table->foreign('user_id')
            ->references('id')
            ->on('users')
            ->onDelete('cascade');

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
        Schema::dropIfExists('bscs_exam_permissions');
    }
}
