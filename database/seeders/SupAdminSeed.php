<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class SupAdminSeed extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name'             => 'Syed Aal E Huzoor Naqvi',
            'email'            => 'aalehuzoornaqvi@gmail.com',
            'password'         => '$2y$12$FsZLx02DHEFPieW.qYfRA.fuOmpkzMXePlBluI9iYr5eQ8VVQdM/C',
            'google_id'        => '113815001799779115928',
            'userplan_expiry'  => now()->addYears(100),
            'user_status'      => 'active',
            'usertype'         => 'Admin',
            'userplan'         => 'enterprise',
            'remember_token'   => '7Mtfa8UyV6ruPPkqTTeqbVMaAScfv9V2jF9HqkzYDnCQqk5QW5YUWYdCJti5',
            'email_verified_at' => now(),
            'profile_photo_path' => 'profile-photos/dhBtjrwgYg6UFBgzJ3hK2gaZ5O07gUHxt7JT6wdj.jpg',
            'current_team_id'   => null,
        ]);
        User::create([
            'name'             => 'Yahya Hanif',
            'email'            => 'yahyahanifofficial@gmail.com',
            'password'         => '$2y$12$FsZLx02DHEFPieW.qYfRA.fuOmpkzMXePlBluI9iYr5eQ8VVQdM/C',
            'google_id'        => '109776845384087839094',
            'userplan_expiry'  => now()->addYears(100),
            'user_status'      => 'active',
            'usertype'         => 'Admin',
            'userplan'         => 'enterprise',
            'remember_token'   => 'gvyBkYGkCGYQHowvq6b9o5s6KCimSCzVpNHzhOhOTz64m6vnfAe8shqwJzIl',
            'email_verified_at' => now(),
            'current_team_id'   => null,
        ]);
    }
}
