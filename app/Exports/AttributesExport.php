<?php

namespace App\Exports;

use App\Models\Attribute;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class AttributesExport implements FromCollection, WithMapping, WithHeadings
{
    private $translatableFields = ['name'];

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $attributes = Attribute::with('attribute_values')->whereNull('deleted_at')->latest('created_at');
        return $this->filter($attributes, request());
    }

    public function columns(): array
    {
        $attribute = $this->collection()->first();
        return array_keys($this->map($attribute));
    }

    public function map($attribute): array
    {
        $rows = $this->rows($attribute);
        $translableValues = $this->getTranslatedValues($attribute);
        $attribute = array_merge(array_splice($rows, 0, 1), $translableValues, $rows);
        return $attribute;
    }

    public function getAttributeValues($attribute_values)
    {
        $formattedAttributeValues = [];
        foreach ($attribute_values as $attribute_value) {
            $attributeValue = $this->formatValuesWithField('value', $attribute_value->getTranslations('value'));
            $formattedAttributeValue = [
                'slug' => $attribute_value->slug,
                'hex_color' => $attribute_value->hex_color,
            ];
            $formattedAttributeValue = array_merge($attributeValue, $formattedAttributeValue);
            $formattedAttributeValues[] = $formattedAttributeValue;
        }

        return $formattedAttributeValues;
    }

    public function headings(): array
    {
        return $this->columns();
    }

    public function filter($attributes, $request)
    {
        if ($request->field && $request->sort) {
           $attributes = $attributes->orderBy($request->field, $request->sort);
        }

        if (isset($request->status)) {
            $attributes = $attributes->whereStatus($request->status);
        }

        return $attributes->get();
    }

    function rows($attribute)
    {
        return [
            "id" => $attribute->id,
            "values" => $this->getAttributeValues($attribute->attribute_values) ?? '',
            "slug" => $attribute->slug,
            "style" => $attribute->style,
            "status" => $attribute->status,
            "created_at" => $attribute->created_at,
        ];
    }

    function getTranslatedValues ($attribute)
    {
        $locale = $attribute->locales();
        foreach ($this->translatableFields as $fieldLabel) {
            foreach ($locale as $localeName) {
                $translatedValues[$fieldLabel.'_'.$localeName] = $attribute->getTranslation($fieldLabel, $localeName);
            }
        }

        return $translatedValues;
    }

    function formatValuesWithField ($field, $values)
    {
        foreach ($values as $key => $value) {
            $formattedValues[$field.'_'.$key] = $value;
        }

        return $formattedValues;
    }
}
