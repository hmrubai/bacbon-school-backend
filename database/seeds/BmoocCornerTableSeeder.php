<?php

use Illuminate\Database\Seeder;

class BmoocCornerTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('bmooc_corners')->insert([
            [
                'title' => "This is title",
                'description' => "This is description of Corner",
                'url' => "http://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ElephantsDream.mp4",
                'thumbnail' => "https://static.bengali.news18.com/static-bengali/2018/03/%E0%A6%AC%E0%A6%BE%E0%A6%82%E0%A6%B2%E0%A6%BE.svg_-630x420.png",
                'duration' => 10000,
                'status' => "Available"
            ],
            [
                'title' => "This is title 2",
                'description' => "This is description 2 of Corner",
                'url' => "http://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ElephantsDream.mp4",
                'thumbnail' => "https://static.bengali.news18.com/static-bengali/2018/03/%E0%A6%AC%E0%A6%BE%E0%A6%82%E0%A6%B2%E0%A6%BE.svg_-630x420.png",
                'duration' => 10000,
                'status' => "Available"
            ],
        ]);
    }
}
