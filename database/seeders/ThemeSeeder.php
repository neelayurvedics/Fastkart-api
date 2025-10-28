<?php

namespace Database\Seeders;

use App\Models\Theme;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ThemeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $themes = [
            [
                'name'   => 'Paris',
                'slug'  => 'paris',
                'status'    => 1,
                'image' => '/admin/images/themes/1.jpg'
            ],
            [
                'name'   => 'Tokyo',
                'slug'  => 'tokyo',
                'status'    => 0,
                'image' => '/admin/images/themes/2.jpg'
            ],
            [
                'name'   => 'Osaka',
                'slug'  => 'osaka',
                'status' => 0,
                'image' => '/admin/images/themes/3.jpg'
            ],
            [
                'name'   => 'Rome',
                'slug'  => 'rome',
                'status' => 0,
                'image' => '/admin/images/themes/4.jpg'
            ],
            [
                'name'   => 'Madrid',
                'slug'  => 'madrid',
                'status' => 0,
                'image' => '/admin/images/themes/5.jpg'
            ],
            [
                'name'   => 'Berlin',
                'slug'  => 'berlin',
                'status' => 0,
                'image' => '/admin/images/themes/6.jpg'
            ],
            [
                'name'   => 'Denver',
                'slug'  => 'denver',
                'status' => 0,
                'image' => '/admin/images/themes/7.jpg'
            ],
            [
                'name'   => 'Moscow',
                'slug'  => 'moscow',
                'status' => 0,
                'image' => '/admin/images/themes/8.jpg'
            ],
            [
                'name'   => 'Cairo',
                'slug'  => 'cairo',
                'status' => 0,
                'image' => '/admin/images/themes/9.jpg'
            ],
        ];

        foreach ($themes as $theme) {
            if (!Theme::where('name', $theme['name'])->first()) {
                Theme::create([
                    'name' => $theme['name'],
                    'slug' => $theme['slug'],
                    'status'=> $theme['status'],
                    'image' => $theme['image']
                ]);
            }
        }

        DB::table('seeders')->updateOrInsert([
            'name' => 'ThemeSeeder',
            'is_completed' => true
        ]);
    }
}
