<?php

namespace Database\Seeders;

use App\Models\HomeScreenSection;
use App\Models\Module;
use Illuminate\Database\Seeder;

class HomeScreenSectionSeeder extends Seeder
{
    public function run()
    {
        // Default sections for grocery module
        $groceryModule = Module::where('module_type', 'grocery')->first();

        if (!$groceryModule) {
            $this->command->warn('Grocery module not found. Skipping HomeScreenSection seeding.');
            return;
        }

        $sections = [
            ['key' => 'highlight', 'title' => 'Highlight'],
            ['key' => 'flash_sale', 'title' => 'Flash Sale'],
            ['key' => 'most_popular', 'title' => 'Most Popular Items'],
            ['key' => 'dynamic_sections', 'title' => 'Dynamic Sections'],
            ['key' => 'middle_banner', 'title' => 'Middle Section Banner'],
            ['key' => 'best_reviewed', 'title' => 'Best Reviewed Items'],
            ['key' => 'just_for_you', 'title' => 'Just For You'],
            ['key' => 'items_you_love', 'title' => 'Items You Love'],
            ['key' => 'promo_code', 'title' => 'Promo Code Banner'],
            ['key' => 'promotional_banner', 'title' => 'Promotional Banner'],
        ];

        foreach ($sections as $index => $section) {
            HomeScreenSection::updateOrCreate(
                [
                    'key' => $section['key'],
                    'module_id' => $groceryModule->id,
                ],
                [
                    'title' => $section['title'],
                    'priority' => $index,
                    'status' => true,
                ]
            );
        }

        $this->command->info('Home screen sections seeded for grocery module.');
    }
}
