<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSchoolAssistancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('school_assistances', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('institute_name');
            $table->string('medium')->nullable();
            $table->string('level')->nullable();
            $table->text('phone_number')->nullable();
            $table->text('email')->nullable();
            $table->text('admission_procedure_url')->nullable();
            $table->text('admission_procedure_text')->nullable();
            $table->text('admission_requirments')->nullable();
            $table->text('contact_address')->nullable();
            $table->string('institute_url')->nullable();
            $table->string('admission_url')->nullable();
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
        Schema::dropIfExists('school_assistances');
    }
}


