<?php

namespace App\Exports;

use App\Models\Tag;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class TagsExport implements FromCollection, WithMapping, WithHeadings
{
    private $translatableFields = ['name','description'];

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Tag::where('type', 'product')->whereNull('deleted_at')->latest('created_at')->get();
    }

    public function columns(): array
    {
        $tag = $this->collection()->first();
        return array_keys($this->map($tag));
    }

    public function map($tag): array
    {
        $rows = $this->rows($tag);
        $translableValues = $this->getTranslatedValues($tag);
        $tag = array_merge(array_splice($rows, 0, 1), $translableValues, $rows);
        return $tag;
    }

    public function headings(): array
    {
        return $this->columns();
    }

    function rows($tag)
    {
        return [
            "id" => $tag->id,
            "type" => $tag->type,
            "slug" => $tag->slug,
            "status" => $tag->status,
            "created_at" => $tag->created_at,
        ];
    }

    function getTranslatedValues ($tag)
    {
        $locale = $tag->locales();
        foreach ($this->translatableFields as $fieldLabel) {
            foreach ($locale as $localeName) {
                $translatedValues[$fieldLabel.'_'.$localeName] = $tag->getTranslation($fieldLabel, $localeName);
            }
        }

        return $translatedValues;
    }
}
