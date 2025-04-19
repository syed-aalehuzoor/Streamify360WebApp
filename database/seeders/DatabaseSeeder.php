<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Server;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        Server::create([
            'name'=>'Storage Server 32TB',
            'ip' => '172.210.20.119',
            'ssh_port' => 22,
            'username' => 'ubuntu',
            'domain' => 'streamify360.net',
            'status' => 'live',
            'type' => 'storage',
            'encoder_type' => 'gpu',
            'public_userid' => 'public',
            'limit' => 1000,
            'total_videos' => 0,
        ]);
        
    }
}
