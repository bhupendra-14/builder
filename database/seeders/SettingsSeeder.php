<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // General
            ['key' => 'site_title', 'value' => 'My Dynamic Website', 'type' => 'string', 'group' => 'general'],
            ['key' => 'site_tagline', 'value' => 'Built with Laravel and Vue.', 'type' => 'string', 'group' => 'general'],

            // SEO
            ['key' => 'meta_description', 'value' => 'A dynamic single-page website managed via the CMS admin.', 'type' => 'text', 'group' => 'seo'],

            // Branding
            ['key' => 'primary_color', 'value' => '#4f46e5', 'type' => 'string', 'group' => 'branding'],

            // Footer — column 1 (brand)
            ['key' => 'footer_about', 'value' => 'A modern page builder for content teams who want to ship faster without waiting on developers.', 'type' => 'text', 'group' => 'footer'],

            // Footer — column 2 (contact)
            ['key' => 'footer_contact_email', 'value' => 'hello@example.com', 'type' => 'string', 'group' => 'footer'],
            ['key' => 'footer_contact_phone', 'value' => '+1 (555) 123-4567', 'type' => 'string', 'group' => 'footer'],
            ['key' => 'footer_contact_address', 'value' => '123 Market Street, Suite 100, San Francisco, CA 94103', 'type' => 'string', 'group' => 'footer'],
        ];

        foreach ($settings as $setting) {
            DB::table('settings')->updateOrInsert(['key' => $setting['key']], $setting);
        }
    }
}
