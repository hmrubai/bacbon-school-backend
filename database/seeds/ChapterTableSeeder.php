<?php

use Illuminate\Database\Seeder;

class ChapterTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('chapters')->insert([
            [
                'name' => "Chapter 1",
                'course_id' => 1,
                'subject_id' => 1,
                'status' => 1,
                'price' => 100,
                'code' => 'CH00001'
            ],
            [
                'name' => "Chapter 2",
                'course_id' => 1,
                'subject_id' => 1,
                'status' => 1,
                'price' => 100,
                'code' => 'CH00002'
            ],
            [
                'name' => "Chapter 3",
                'course_id' => 1,
                'subject_id' => 1,
                'status' => 1,
                'price' => 100,
                'code' => 'CH00003'
            ],
            [
                'name' => "Chapter 1",
                'course_id' => 2,
                'subject_id' => 1,
                'status' => 1,
                'price' => 100,
                'code' => 'CH00004'
            ],
        ]);
    }
}
