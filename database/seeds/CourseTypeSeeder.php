<?php

use Illuminate\Database\Seeder;

class CourseTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('course_types')->insert([
            [
                'name' => "Academic Courses",
                'status' => "active",
            ],
            [
                'name' => "Admission Courses",
                'status' => "active",
            ],
            [
                'name' => "Professional Courses",
                'status' => "active",
            ]
        ]);
    }
}
