<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'name' => "ADMIN",
            'cpf' => '04916744306',
            'email' => 'admin@gmail.com',
            'user_type' => 2,
            'password' => bcrypt('12345678'),
        ]);
    }
}
