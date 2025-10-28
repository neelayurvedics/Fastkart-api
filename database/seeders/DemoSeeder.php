<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DemoSeeder extends Seeder
{
    public function run()
    {
        // Create admin user
        $adminUser = User::create([
            'name' => 'Admin User',
            'email' => 'admin@demo.com',
            'password' => Hash::make('password'),
        ]);
        $adminRole = Role::where('name', 'Admin')->first();
        $adminUser->assignRole($adminRole);

        // Create vendor user
        $vendorUser = User::create([
            'name' => 'Vendor User',
            'email' => 'vendor@demo.com',
            'password' => Hash::make('password'),
        ]);
        $vendorRole = Role::where('name', 'Vendor')->first();
        $vendorUser->assignRole($vendorRole);

        // Create customer user
        $customerUser = User::create([
            'name' => 'Customer User',
            'email' => 'customer@demo.com',
            'password' => Hash::make('password'),
        ]);
        $customerRole = Role::where('name', 'Customer')->first();
        $customerUser->assignRole($customerRole);

        // Create store
        $store = Store::create([
            'store_name' => 'Demo Store',
            'slug' => 'demo-store',
            'vendor_id' => $vendorUser->id,
            'status' => 1,
            'is_approved' => 1,
            'address' => '123 Demo Street',
            'description' => 'This is a demo store for testing purposes',
        ]);

        // Create categories
        $categories = [
            ['name' => ['en' => 'Electronics'], 'slug' => 'electronics', 'created_by_id' => $adminUser->id],
            ['name' => ['en' => 'Fashion'], 'slug' => 'fashion', 'created_by_id' => $adminUser->id],
            ['name' => ['en' => 'Home & Living'], 'slug' => 'home-living', 'created_by_id' => $adminUser->id],
            ['name' => ['en' => 'Books'], 'slug' => 'books', 'created_by_id' => $adminUser->id],
        ];

        foreach ($categories as $categoryData) {
            $category = new Category();
            $category->setTranslations('name', $categoryData['name']);
            $category->slug = $categoryData['slug'];
            $category->created_by_id = $categoryData['created_by_id'];
            $category->save();
        }

        // Create brands
        $brands = [
            ['name' => ['en' => 'Apple'], 'slug' => 'apple', 'created_by_id' => $adminUser->id],
            ['name' => ['en' => 'Samsung'], 'slug' => 'samsung', 'created_by_id' => $adminUser->id],
            ['name' => ['en' => 'Nike'], 'slug' => 'nike', 'created_by_id' => $adminUser->id],
            ['name' => ['en' => 'Adidas'], 'slug' => 'adidas', 'created_by_id' => $adminUser->id],
        ];

        foreach ($brands as $brandData) {
            $brand = new Brand();
            $brand->setTranslations('name', $brandData['name']);
            $brand->slug = $brandData['slug'];
            $brand->created_by_id = $brandData['created_by_id'];
            $brand->save();
        }

        // Create attributes
        $sizeAttribute = new Attribute();
        $sizeAttribute->name = ['en' => 'Size'];
        $sizeAttribute->slug = 'size';
        $sizeAttribute->created_by_id = $adminUser->id;
        $sizeAttribute->save();

        $colorAttribute = new Attribute();
        $colorAttribute->name = ['en' => 'Color'];
        $colorAttribute->slug = 'color';
        $colorAttribute->created_by_id = $adminUser->id;
        $colorAttribute->save();

        // Create attribute values
        $sizes = ['S', 'M', 'L', 'XL'];
        foreach ($sizes as $size) {
            $value = new AttributeValue();
            $value->attribute_id = $sizeAttribute->id;
            $value->value = ['en' => $size];
            $value->created_by_id = $adminUser->id;
            $value->save();
        }

        $colors = ['Red', 'Blue', 'Green', 'Black', 'White'];
        foreach ($colors as $color) {
            $value = new AttributeValue();
            $value->attribute_id = $colorAttribute->id;
            $value->value = ['en' => $color];
            $value->created_by_id = $adminUser->id;
            $value->save();
        }

        // Create tags
        $tags = [
            ['name' => ['en' => 'New Arrival'], 'slug' => 'new-arrival', 'created_by_id' => $adminUser->id],
            ['name' => ['en' => 'Best Seller'], 'slug' => 'best-seller', 'created_by_id' => $adminUser->id],
            ['name' => ['en' => 'Featured'], 'slug' => 'featured', 'created_by_id' => $adminUser->id],
            ['name' => ['en' => 'Sale'], 'slug' => 'sale', 'created_by_id' => $adminUser->id],
        ];

        foreach ($tags as $tagData) {
            $tag = new Tag();
            $tag->setTranslations('name', $tagData['name']);
            $tag->slug = $tagData['slug'];
            $tag->created_by_id = $tagData['created_by_id'];
            $tag->save();
        }

        // Create products
        $products = [
            [
                'data' => [
                    'name' => ['en' => 'iPhone 14 Pro'],
                    'slug' => 'iphone-14-pro',
                    'description' => ['en' => 'Latest iPhone with advanced features'],
                    'price' => 999.99,
                    'sale_price' => 949.99,
                    'sku' => 'IP14PRO-001',
                    'quantity' => 100,
                    'status' => 1,
                    'store_id' => $store->id,
                    'brand_id' => Brand::where('slug', 'apple')->first()->id,
                    'created_by_id' => $vendorUser->id,
                    'product_type' => 'physical',
                ],
                'category_slug' => 'electronics'
            ],
            [
                'data' => [
                    'name' => ['en' => 'Nike Air Max'],
                    'slug' => 'nike-air-max',
                    'description' => ['en' => 'Comfortable running shoes'],
                    'price' => 129.99,
                    'sale_price' => 99.99,
                    'sku' => 'NAM-001',
                    'quantity' => 200,
                    'status' => 1,
                    'store_id' => $store->id,
                    'brand_id' => Brand::where('slug', 'nike')->first()->id,
                    'created_by_id' => $vendorUser->id,
                    'product_type' => 'physical',
                ],
                'category_slug' => 'fashion'
            ],
            [
                'data' => [
                    'name' => ['en' => 'Samsung Smart TV'],
                    'slug' => 'samsung-smart-tv',
                    'description' => ['en' => '65-inch 4K Smart TV'],
                    'price' => 1299.99,
                    'sale_price' => 1099.99,
                    'sku' => 'SST-001',
                    'quantity' => 50,
                    'status' => 1,
                    'store_id' => $store->id,
                    'brand_id' => Brand::where('slug', 'samsung')->first()->id,
                    'created_by_id' => $vendorUser->id,
                    'product_type' => 'physical',
                ],
                'category_slug' => 'electronics'
            ],
        ];

        foreach ($products as $productData) {
            $product = new Product();
            $product->setTranslations('name', $productData['data']['name']);
            $product->setTranslations('description', $productData['data']['description']);
            $product->slug = $productData['data']['slug'];
            $product->price = $productData['data']['price'];
            $product->sale_price = $productData['data']['sale_price'];
            $product->sku = $productData['data']['sku'];
            $product->quantity = $productData['data']['quantity'];
            $product->status = $productData['data']['status'];
            $product->store_id = $productData['data']['store_id'];
            $product->brand_id = $productData['data']['brand_id'];
            $product->created_by_id = $productData['data']['created_by_id'];
            $product->product_type = $productData['data']['product_type'];
            $product->save();

            $category = Category::where('slug', $productData['category_slug'])->first();
            if ($category) {
                $product->categories()->attach($category->id);
            }
            
            // Attach some tags to each product
            $tag = Tag::where('slug', 'new-arrival')->first();
            if ($tag) {
                $product->tags()->attach($tag->id);
            }
            if ($product->sale_price < $product->price) {
                $saleTag = Tag::where('slug', 'sale')->first();
                if ($saleTag) {
                    $product->tags()->attach($saleTag->id);
                }
            }
        }
    }
}