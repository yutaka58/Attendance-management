<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdminUserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $adminUsers = [
            [
                'name' => 'admintest',
                'email' => 'admintest@example.com',
                'password' => bcrypt('password'),
            ]
        ];
        DB::table('admin_users')->insert($adminUsers);
    }
}
