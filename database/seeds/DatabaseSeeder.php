<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(AdminTable::class);
        $this->call(UsersTableSeeder::class);
        $this->call(CourseTypeSeeder::class);
        $this->call(CourseTableSeeder::class);
        $this->call(SubjectTableSeeder::class);
        $this->call(ChapterTableSeeder::class);
        $this->call(BmoocCornerTableSeeder::class);
        $this->call(CourseSubjectTableSeeder::class);
        $this->call(LectureVideoTableSeeder::class);
    }
}
