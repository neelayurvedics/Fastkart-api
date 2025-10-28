<?php

namespace App\Exports;

use App\Enums\RoleEnum;
use App\Models\Product;
use App\Helpers\Helpers;
use Maatwebsite\Excel\Concerns\FromQuery;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class ProductsExport implements FromQuery, WithMapping, WithHeadings, WithChunkReading, ShouldQueue
{
    use Exportable;

    /**
     * @return \Illuminate\Support\Collection
     */
    public function query()
    {
        $product = Product::whereNull('deleted_at')->latest('created_at');
        return $this->filter($product, request());
    }

    public function chunkSize(): int
    {
        return 500;
    }

    private $translatableFields = ['name','short_description','description','meta_title','meta_description','estimated_delivery_text','return_policy_text'];
    public function columns(): array
    {
        $product = $this->query()->first();
        return array_keys($this->map($product));
    }

    public function map($product): array
    {
        $rows = $this->rows($product);
        $translateValues = $this->getTranslatedValues($product);
        $product = array_merge(array_splice($rows, 0, 1), $translateValues, $rows);
        return $product;
    }

    public function getVariation($variations)
    {
        $formattedVariations = [];
        foreach ($variations as $variation) {
            $name = $this->formatValuesWithField('name', $variation->getTranslations('name'));
            $formattedVariation = [
                'price' => $variation->price,
                'discount' => $variation->discount ?? 0,
                'stock_status' => $variation->stock_status,
                'sku' => $variation->sku,
                'quantity' => $variation->quantity,
                'variation_image_url' => $variation?->variation_image?->original_url,
                'digital_files_url' =>  $variation?->digital_files?->pluck('original_url')?->implode(','),
                'attribute_values' => $variation?->attribute_values?->pluck('id')?->toArray(),
                'is_licensable' => $variation->is_licensable,
                'is_licensekey_auto' => $variation->is_licensekey_auto,
                'license_key' =>  $variation->license_key?->pluck('license_key')?->implode(','),
                'variation_galleries_url' => $variation?->variation_galleries?->pluck('original_url')?->toArray(),
                'separator' => $variation->separator,
                'status' => $variation->status
            ];
            $formattedVariation = array_merge($name, $formattedVariation);
            $formattedVariations[] = $formattedVariation;
        }

        return $formattedVariations;
    }

    public function headings(): array
    {
        return $this->columns();
    }

    public function filter($product, $request)
    {
        if (Helpers::isUserLogin()) {
            $roleName = Helpers::getCurrentRoleName();
            if ($roleName == RoleEnum::VENDOR) {
                $product = $product->where('store_id', Helpers::getCurrentVendorStoreId());
            }
        }

        if ($request->field && $request->sort) {
            $product = $product->orderBy($request->field, $request->sort);
        }

        if (isset($request->status)) {
            $product = $product->where('status',$request->status);
        }

        if ($request->store_ids) {
            $store_ids = explode(',', $request->store_ids);
            $product = $product->whereIn('store_id', $store_ids);
        }

        if ($request->category_ids) {
            $category_ids = explode(',', $request->category_ids);
            $product = $product->whereRelation('categories', function($categories) use ($category_ids) {
                $categories->WhereIn('category_id', $category_ids);
            });
        }

        if ($request->tag_ids) {
            $tag_ids = explode(',', $request->tag_ids);
            $product = $product->whereRelation('tags', function($tags) use ($tag_ids) {
                $tags->WhereIn('tag_id', $tag_ids);
            });
        }

        if ($request->brand_ids) {
            $brand_ids = explode(',', $request->brand_ids);
            $product = $product->whereRelation('brand', function($brands) use ($brand_ids) {
                $brands->WhereIn('brand_id', $brand_ids);
            });
        }

        if ($request->product_type) {
            $product = $product->where('product_type', $request->product_type);
        }

        return $product;
    }

    function rows($product)
    {
        return [
            'id' => $product->id,
            'product_type' => $product->product_type,
            'type' => $product->type,
            'unit' => $product->unit,
            'quantity' => $product->quantity,
            'weight' => $product->weight,
            'price' => $product->price,
            'sale_price' => $product->sale_price,
            'discount' => $product->discount,
            'sku' => $product->sku,
            'stock_status' => $product->stock_status,
            'store_id' => $product->store_id,
            'is_free_shipping' => $product->is_free_shipping,
            'is_featured' => $product->is_featured,
            'is_return' => $product->is_return,
            'is_trending' => $product->is_trending,
            'is_sale_enable' => $product->is_sale_enable,
            'is_random_related_products' => $product->is_random_related_products,
            'is_external' => $product->is_external,
            'external_url' => $product->external_url,
            'external_button_text' => $product->external_button_text,
            'shipping_days' => $product->shipping_days,
            'sale_starts_at' => $product->sale_starts_at,
            'sale_expired_at' => $product->sale_expired_at,
            'show_stock_quantity' => $product->show_stock_quantity,
            'safe_checkout' => $product->safe_checkout,
            'secure_checkout' => $product->secure_checkout,
            'social_share' => $product->social_share,
            'encourage_order' => $product->encourage_order,
            'encourage_view' => $product->encourage_view,
            'is_approved' => $product->is_approved,
            'brand_id' => $product->brand_id,
            'is_digital' => $product->is_digital,
            'is_licensable' => $product->is_licensable,
            'preview_url' => $product->preview_url,
            'watermark' => $product->watermark,
            'watermark_position' => $product->watermark_position,
            'wholesale_price_type' => $product->wholesale_price_type,
            'is_licensekey_auto' => $product->is_licensekey_auto,
            'separator' => $product->separator,
            'preview_type' => $product->preview_type,
            'created_at' => $product->created_at,
            'updated_at' => $product->updated_at,
            'deleted_at' => $product->deleted_at,
            'status' => $product->status,
            'product_thumbnail_url' => $product->product_thumbnail?->original_url,
            'product_meta_image_url' => $product->product_meta_image?->original_url,
            'size_chart_image_url' => $product->size_chart_image?->original_url,
            'watermark_image_url' => $product->watermark_image?->original_url,
            'preview_audio_file_url' => $product->preview_audio_file?->original_url,
            'preview_video_file_url' => $product->preview_video_file?->original_url,
            'product_galleries_url' => $product->product_galleries?->pluck('original_url')->implode(','),
            'digital_files_url' => $product->digital_files?->pluck('original_url')->implode(','),
            'attributes' => $product->attributes?->pluck('id')->implode(','),
            'categories' => $product->categories?->pluck('id')->implode(','),
            'tags' => $product->tags?->pluck('id')->implode(','),
            'wholesale_prices' => $product->wholesales ?? '',
            'license_key' => $product->license_keys?->pluck('license_key')->implode(','),
            'variations' => $this->getVariation($product->variations)?? '',
            'external_details' => $product->external_details,
            'publication_id' => $product->publication_id,
            'authors_id' => $product->authors_id,
        ];
    }

    function getTranslatedValues ($product)
    {
        $locale = $product->locales();
        foreach ($this->translatableFields as $fieldLabel) {
            foreach ($locale as $localeName) {
                $translatedValues[$fieldLabel.'_'.$localeName] = $product->getTranslation($fieldLabel, $localeName);
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
