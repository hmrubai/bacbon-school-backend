<?php

use Illuminate\Database\Seeder;

class LectureVideoTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('lecture_videos')->insert([
            [
                'course_id' => 1,
                'subject_id' => 1,
                'code' => 'CH00001',
                'chapter_id' => 1,
                'title' => "This is title",
                'description' => "This is description of Corner",
                'url' => "http://bmoocapi.bacbonltd.com/uploads/lecture_videos/subject3_chapter2_lecture2.mp4",
                'thumbnail' => "http://bmoocapi.bacbonltd.com/uploads/thumbnails/chap2lecture1Thumbnails.png",
                'duration' => 360,
                'isFree' => true,
                'status' => "Available",
                'price' => 30
            ],
            [
                'course_id' => 1,
                'subject_id' => 1,
                'chapter_id' => 1,
                'code' => 'CH00002',
                'title' => "This is title 2",
                'description' => "This is description 2",
                'url' => "http://bmoocapi.bacbonltd.com/uploads/lecture_videos/subject3_chapter2_lecture2_2.mp4",
                'thumbnail' => "http://bmoocapi.bacbonltd.com/uploads/thumbnails/chap2lecture2Thumbnails.png",
                'duration' => 450,
                'isFree' => true,
                'status' => "Available",
                'price' => 20
            ]
        ]);
    }
}
