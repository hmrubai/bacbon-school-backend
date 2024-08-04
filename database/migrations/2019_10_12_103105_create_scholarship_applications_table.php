<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateScholarshipApplicationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('divisions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 100);
            $table->string('name_bn', 100)->nullable();
            $table->string('name_jp', 100)->nullable();
        });
        Schema::create('districts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('division_id')->unsigned();
            $table->string('name', 100);
            $table->string('name_bn', 100)->nullable();
            $table->string('name_jp', 100)->nullable();

            $table->foreign('division_id')
            ->references('id')
            ->on('divisions')
            ->onDelete('cascade');
        });
        Schema::create('thanas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('district_id')->unsigned();
            $table->string('name', 100);
            $table->string('name_bn', 100);
            $table->string('name_jp', 100);

            $table->foreign('district_id')
            ->references('id')
            ->on('districts')
            ->onDelete('cascade');
        });
        Schema::create('scholarship_applications', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_id')->unsigned();
            $table->string('name', 100);
            $table->date('date_of_birth');
            $table->string('sex', 20);
            $table->text('address');
            $table->string('country', 80);
            $table->bigInteger('division_id')->unsigned()->nullable();
            $table->bigInteger('district_id')->unsigned()->nullable();
            $table->bigInteger('thana')->unsigned()->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->string('email', 50)->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('fax', 50)->nullable();
            $table->string('first_language', 50)->nullable();
            $table->string('other_language', 50)->nullable();
            $table->string('father_name', 150)->nullable();
            $table->string('father_occupation', 100)->nullable();
            $table->double('father_yearly_income')->nullable();
            $table->string('father_contact_no', 20)->nullable();
            $table->string('mother_name', 150)->nullable();
            $table->string('mother_occupation', 100)->nullable();
            $table->double('mother_yearly_income')->nullable();
            $table->string('mother_contact_no', 20)->nullable();
            $table->string('reference_1_name', 150)->nullable();
            $table->string('reference_1_occupation', 100)->nullable();
            $table->string('reference_1_relation', 100)->nullable();
            $table->string('reference_1_contact_no', 20)->nullable();
            $table->string('reference_1_address', 250)->nullable();
            $table->string('reference_2_name', 150)->nullable();
            $table->string('reference_2_occupation', 100)->nullable();
            $table->string('reference_2_relation', 100)->nullable();
            $table->string('reference_2_contact_no', 20)->nullable();
            $table->string('reference_2_address', 250)->nullable();
            $table->text('message')->nullable();
            $table->timestamps();

            $table->foreign('user_id')
            ->references('id')
            ->on('users')
            ->onDelete('cascade');

            $table->foreign('division_id')
            ->references('id')
            ->on('divisions')
            ->onDelete('cascade');

            $table->foreign('district_id')
            ->references('id')
            ->on('districts')
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
        Schema::dropIfExists('scholarship_applications');
    }
}
