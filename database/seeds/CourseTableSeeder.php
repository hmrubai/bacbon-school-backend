<?php

use Illuminate\Database\Seeder;

class CourseTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('courses')->insert([
            [
                'name' => "SSC",
                'course_type_id' => 1,
                'status' => "active",
                'code' => 'CS00001',
                'price' => 1000,
            ],
            [
                'name' => "HSC",
                'course_type_id' => 1,
                'code' => 'CS00002',
                'status' => "active",
                'price' => 1000,
            ]
        ]);
    }
}
