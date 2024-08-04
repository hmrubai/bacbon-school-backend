<?php

use Illuminate\Database\Seeder;

class CourseSubjectTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('course_subjects')->insert([
            [
                'course_id' => 1,
                'subject_id' => 1,
                'code' => 'CH00001',
                'price' => 300,
                'keywords' => 'Keywords',
                'status' => "Available"
            ],
            [
                'course_id' => 1,
                'subject_id' => 2,
                'code' => 'CH00002',
                'price' => 300,
                'keywords' => 'Keywords2',
                'status' => "Available"
            ],
            [
                'course_id' => 2,
                'subject_id' => 1,
                'code' => 'CH00003',
                'price' => 300,
                'keywords' => 'Keywords3',
                'status' => "Available"
            ],
            [
                'course_id' => 1,
                'subject_id' => 3,
                'code' => 'CH00004',
                'price' => 300,
                'keywords' => 'Keywords4',
                'status' => "Available"
            ],
        ]);
    }
}
