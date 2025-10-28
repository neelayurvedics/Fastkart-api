<?php

namespace App\Exports;

use App\Models\Brand;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class BrandsExport implements FromCollection, WithMapping, WithHeadings
{
    private $translatableFields = ['name','meta_title','meta_description'];
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $brands = Brand::whereNull('deleted_at')->latest('created_at');
        return $this->filter($brands, request());
    }

    public function columns(): array
    {
        $brand = $this->collection()->first();
        return array_keys($this->map($brand));
    }

    public function map($brand): array
    {
        $rows = $this->rows($brand);
        $translableValues = $this->getTranslatedValues($brand);
        $brand = array_merge(array_splice($rows, 0, 1), $translableValues, $rows);
        return $brand;
    }

    public function headings(): array
    {
        return $this->columns();
    }

    function rows($brand)
    {
        return [
            "id" => $brand->id,
            "slug" => $brand->slug,
            "status" => $brand->status,
            "brand_image_url" => $brand->brand_image?->original_url,
            "brand_banner_url" => $brand->brand_banner_image?->original_url,
            "brand_meta_image_url" => $brand->brand_meta_image?->original_url,
            "created_at" => $brand->created_at
        ];
    }

    public function filter($brands, $request)
    {
        if ($request->ids) {
            $ids = explode(',',$request->ids);
            $brands = $brands->whereIn('id', $ids);
        }

        if ($request->field && $request->sort) {
           $brands = $brands->orderBy($request->field, $request->sort);
        }

        if (isset($request->status)) {
            $brands = $brands->whereStatus($request->status);
        }

        if ($request->search) {
            $brands = $brands->where('name', 'like', '%'.$request->search.'%');
        }

        return $brands->get();
    }

    function getTranslatedValues ($brand)
    {
        $locale = $brand->locales();
        foreach ($this->translatableFields as $fieldLabel) {
            foreach ($locale as $localeName) {
                $translatedValues[$fieldLabel.'_'.$localeName] = $brand->getTranslation($fieldLabel, $localeName);
            }
        }

        return $translatedValues;
    }
}
