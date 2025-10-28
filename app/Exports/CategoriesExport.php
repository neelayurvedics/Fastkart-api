<?php

namespace App\Exports;

use App\Models\Category;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class CategoriesExport implements FromCollection, WithMapping, WithHeadings
{
    private $translatableFields = ['name','description'];

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Category::where('type', 'product')->whereNull('deleted_at')->latest('created_at')->get();
    }

    public function columns(): array
    {
        $category = $this->collection()->first();
        return array_keys($this->map($category));
    }

    public function map($category): array
    {
        $rows = $this->rows($category);
        $translableValues = $this->getTranslatedValues($category);
        $category = array_merge(array_splice($rows, 0, 1), $translableValues, $rows);
        return $category;
    }

    public function headings(): array
    {
        return $this->columns();
    }

    function rows($category)
    {
        return [
            'id' => $category->id,
            'slug' => $category->slug,
            'type' => $category->type,
            'parent_id' => $category->parent_id,
            'commission_rate' => $category->commission_rate,
            'status' => $category->status,
            'category_image_url' => $category->category_image?->original_url,
            'category_icon_url' => $category->category_icon?->original_url,
            'is_allow_all_zone' => $category->is_allow_all_zone,
            'exclude_zone_ids' => $category->exclude_zones,
            'zone_ids' => $category->zones,
            'created_at' => $category->created_at
        ];
    }

    function getTranslatedValues ($category)
    {
        $locale = $category->locales();
        foreach ($this->translatableFields as $fieldLabel) {
            foreach ($locale as $localeName) {
                $translatedValues[$fieldLabel.'_'.$localeName] = $category->getTranslation($fieldLabel, $localeName);
            }
        }

        return $translatedValues;
    }
}
