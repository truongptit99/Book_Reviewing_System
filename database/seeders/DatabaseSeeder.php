<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        DB::table('roles')->insert([
            ['name' => 'admin'],
            ['name' => 'user'],
        ]);

        DB::table('users')->insert([
            'username' => 'admin',
            'password' => Hash::make(123456),
            'email' => 'admin@gmail.com',
            'fullname' => 'Admin',
            'dob' => '1999-04-12',
            'is_active' => config('app.is_active'),
            'role_id' => config('app.admin_role_id'),
        ]);

        DB::table('images')->insert([
            'path' => 'default-ava.jpg',
            'imageable_type' => User::class,
            'imageable_id' => 1,
        ]);

        DB::table('categories')->insert([
            [
                'name' => 'Tâm Lý - Kỹ Năng Sống',
                'parent_id' => 0,
            ],
            [
                'name' => 'Y Học - Sức Khỏe',
                'parent_id' => 0,
            ],
            [
                'name' => 'Thể Thao - Nghệ Thuật',
                'parent_id' => 0,
            ],
            [
                'name' => 'Tử Vi - Phong Thủy',
                'parent_id' => 0,
            ],
            [
                'name' => 'Kiến Trúc - Xây Dựng',
                'parent_id' => 0,
            ],
        ]);
    }
}
