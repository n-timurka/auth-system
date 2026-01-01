<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SiteSetting;

class SiteSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // General Segment
            [
                'key' => 'site_name',
                'value' => 'Auth System',
                'segment' => 'general',
                'type' => 'string',
                'description' => 'The name of the application',
            ],
            [
                'key' => 'site_description',
                'value' => 'Laravel Authentication System',
                'segment' => 'general',
                'type' => 'string',
                'description' => 'A brief description of the application',
            ],

            // Auth Segment
            [
                'key' => 'remember_me_enabled',
                'value' => 'true',
                'segment' => 'auth',
                'type' => 'boolean',
                'description' => 'Enable or disable the "Remember Me" option on login',
            ],
            [
                'key' => 'session_lifetime',
                'value' => '120',
                'segment' => 'auth',
                'type' => 'integer',
                'description' => 'Session lifetime in minutes',
            ],
        ];

        foreach ($settings as $setting) {
            SiteSetting::upsert($setting, ['key']);
        }
    }
}
