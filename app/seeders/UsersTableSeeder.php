<?php namespace App\Seeders;

use App\Models\User;
use Kernel\Hash;

/**
 * Class UsersTableSeeder
 *
 * @package App\Seeders
 */
class UsersTableSeeder
{
    /**
     * Seed the database table
     */
    public function __construct()
    {
        User::insert([
            'firstname' => 'John',
            'lastname' => 'Doe',
            'username' => 'username',
            'password' => Hash::encode('password'),
            'email' => 'johndoe@mailserver.dev',
            'number' => 010000040120,
            'avatar' => 'default.jpg',
            'role' => 'superadmin',
            'active' => 'yes',
            'date_added' => date_now(),
            'time_added' => time_now(),
        ]);
    }
}