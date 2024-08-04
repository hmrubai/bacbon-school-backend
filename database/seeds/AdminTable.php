<?php

use Illuminate\Database\Seeder;

class AdminTable extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::table('admins')->insert([
            [
                'username' => "rueen",
                'name' => "Mehedi Rueen",
                'email' => "rueen@gmail.com",
                'address' => "Gulshan-2, Dhaka",
                'gender' => "Male",
                'role' => "super admin",
                'password' => bcrypt('12345678')
            ],
            [
                'username' => "admin",
                'name' => "Mehedi Rueen",
                'email' => "admin@gmail.com",
                'address' => "Gulshan-2, Dhaka",
                'gender' => "Male",
                'role' => "admin",
                'password' => bcrypt('12345678')
            ]
        ]);
    }
}
