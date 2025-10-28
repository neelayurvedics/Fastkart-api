<?php

namespace Database\Seeders;

use App\Models\language;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $language = Language::updateOrCreate(['locale' => 'en'], [
            'name' => 'English',
            'locale' => 'en',
            'flag' => 'in',
            'is_rtl' => false,
            'status' => true,
            'system_reserve' => true
        ]);

        if (isset($language)) {
            $language->setTranslation('name', 'en', 'English')->save();
        }

        DB::table('seeders')->updateOrInsert([
            'name' => 'LanguageSeeder',
            'is_completed' => true
        ]);
    }
}
