<?php

use Illuminate\Database\Seeder;

class SubjectTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('subjects')->insert([
            [
                'name' => "Mathematics",
                'color_name' => "red",
            ],
            [
                'name' => "English",
                'color_name' => "orange",
            ],
            [
                'name' => "Science",
                'color_name' => "sky",
            ]
        ]);
    }
}
