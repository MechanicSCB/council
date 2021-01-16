<?php

use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::truncate();
        //
        DB::table('users')->insert([
            'name' => 'JohnDoe',
            'email' => 'johndoe@example.com',
            'password' => Hash::make('johndoe@example.com'),
            'confirmed' => 1,
        ]);

        DB::table('users')->insert([
            'name' => 'JaneDoe',
            'email' => 'janedoe@example.com',
            'password' => Hash::make('janedoe@example.com'),
            'confirmed' => 1,
        ]);
    }
}
