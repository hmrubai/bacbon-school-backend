<?php

use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            [
                'name' => "Mehedi Rueen",
                'email' => "runfast@gmail.com",
                'mobile_number' => "01740131090",
                'address' => "Gulshan-2, Dhaka",
                'current_course_id' => 2,
            ],
            [
                'name' => "Abdur Rouf",
                'email' => "rouf1@gmail.com",
                'mobile_number' => "01746565106",
                'address' => "Banashree, Dhaka",
                'current_course_id' => 1,
            ]
        ]);
    }
}
