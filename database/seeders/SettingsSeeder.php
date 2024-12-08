<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingsSeeder extends Seeder
{
    public function run()
    {
        $settings = [
            ['key' => 'GOOGLE_CLIENT_ID', 'value' => ''],
            ['key' => 'GOOGLE_CLIENT_SECRET', 'value' => ''],
            ['key' => 'GOOGLE_REDIRECT_URI', 'value' => ''],

            ['key' => 'AZURE_STORAGE_NAME', 'value' => 'hlsencoder'],
            ['key' => 'AZURE_STORAGE_KEY', 'value' => ''],
            ['key' => 'AZURE_STORAGE_CONTAINER', 'value' => 'laravel'],
            ['key' => 'AZURE_STORAGE_URL', 'value' => 'https://hlsencoder.blob.core.windows.net/'],
            ['key' => 'AZURE_STORAGE_ENDPOINT', 'value' => ''],

            ['key' => 'BASIC_PLAN_THREADS', 'value' => '4'],
            ['key' => 'PREMIUM_PLAN_THREADS', 'value' => '8'],
            ['key' => 'ENTERPRISE_PLAN_THREADS', 'value' => '16'],
        ];

        DB::table('settings')->insert($settings);
    }
}

