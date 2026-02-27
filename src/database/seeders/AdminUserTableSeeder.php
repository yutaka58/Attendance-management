<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class AdminUserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $adminusers ([
        'name' => 'admintest',
        'email' => 'admintest@example.com',
        'password' => bcrypt('password'),
        ]);
        DB::table('adminusers')->insert($adminusers);

    }
}
