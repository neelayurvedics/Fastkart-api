<?php

namespace App\Http\Resources;

use App\Enums\ProductType;
use Illuminate\Http\Request;

class ProductResource  extends BaseResource
{
    protected $showSensitiveAttributes = true;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->fetchTranslation('name'),
            'is_all_tags' => $this->is_all_tags,
            'is_all_categories' => $this->is_all_categories,
            'is_all_author' => $this->is_all_author,
            'short_description' => $this->fetchTranslation('short_description'),
            'type' => $this->type,
            'unit' => $this->unit,
            'quantity' => $this->quantity,
            'price' => $this->price,
            'sale_price' => $this->sale_price,
            'discount' => $this->discount,
            'is_featured' => $this->is_featured,
            'is_digital' => $this->is_digital,
            'product_type' => $this->product_type,
            'wholesale_price_type' => $this->wholesale_price_type,
            'brand_id' => $this->brand_id,
            'is_external' => $this->is_external,
            'external_button_text' => $this->external_button_text,
            'external_url' => $this->external_url,
            'is_sale_enable' => $this->is_sale_enable,
            'sale_starts_at' => $this->sale_starts_at,
            'sale_expired_at' => $this->sale_expired_at,
            'is_return' => $this->is_return,
            'is_trending' => $this->is_trending,
            'is_approved' => $this->is_approved,
            'is_wishlist' => $this->is_wishlist,
            'sku' => $this->sku,
            'stock_status' => $this->stock_status,
            'product_thumbnail_id' => $this->product_thumbnail_id,
            'slug' => $this->slug,
            'store_id' => $this->store_id,
            'wholesales' => $this->wholesales,
            'variations' => $this->getVariationAttributes(),
            'product_thumbnail' => $this->product_thumbnail,
            'product_galleries' => $this->product_galleries->toArray(),
            'attributes' => $this->attributes,
            'brand' => $this->brand,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'is_file' => $this->getIsFileExists($this),
            'store' => $this->getStoreAttributes(),
            'categories' => $this->categories,
            'reviews_count' => $this->reviews_count,
            'rating_count' => $this->rating_count,
            'authors_id' => $this->authors_id,
            'author_id' => $this->author_id,
            'orders_count'  => $this->orders_count,
             $this->mergeWhen(($request->top_selling && $request->filter_by), [
                'order_amount' => ($request->top_selling && $request->filter_by) ? $this->order_amount: 0,
            ]),
        ];
    }

    public function getIsFileExists($product)
    {
        return $product->digital_files()->exists()? '1': '0';
    }

    public function getVariationAttributes()
    {
        if ($this->variations) {
            return $this->variations->map(function ($variation) {
                return collect($variation)->except([
                    'variation_options',
                    'separator',
                    'is_licensable',
                    'is_licensekey_auto',
                    'deleted_at',
                    'updated_at'
                ]);
            });
        }
    }

    public function getStoreAttributes()
    {
        if ($this->store) {
            return $this->store?->only([
                'id',
                'store_name',
                'slug',
            ]);
        }
    }

    public function fetchTranslation($key)
    {
        $translation = $this->getTranslation($key, app()->getLocale());
        $defaultTranslation = $translation ?? $products[$key];

        if (empty($defaultTranslation)) {
            return $this->getDatabaseValue($key);
        }

        return $defaultTranslation;
    }

    public function getDatabaseValue($key)
    {
        return $this->getRawOriginal($key);
    }
}
