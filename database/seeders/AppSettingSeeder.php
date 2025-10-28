<?php

namespace Database\Seeders;

use App\Models\AppSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AppSettingSeeder extends Seeder
{
    protected $baseName;

    public function __construct()
    {
        $this->baseName = config('app.name');
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */

    public function run()
    {
        $values = [
            "blogs" => [
                "title" => "Featured Blog",
                "status" => true,
                "blog_ids" => null,
                "sub_title" => "Uncover Intriguing Highlights in Our Featured Blog",
            ],
            "brands" => [
                "title" => "Brands Showcase",
                "status" => true,
                "brand_ids" => null,
            ],
            "seller" => [
                "title" => "Sellers Showcase",
                "status" => true,
                "store_ids" => null,
                "sub_title" =>
                    "Explore our top-rated products loved by customers just like you. Find your new favorites today!",
            ],
            "coupons" => [
                "title" => "Coupons",
                "status" => true,
                "sub_title" => "Bank & Wallet Offers",
                "coupon_ids" => null,
            ],
            "home_banner" => [
                "status" => true,
                "banners" => [
                    [
                        "status" => true,
                        "image_url" => null,
                        "redirect_link" => [
                            "link" => null,
                            "link_type" => null
                        ],
                    ],
                    [
                        "status" => true,
                        "image_url" => null,
                        "redirect_link" => [
                            "link" => null
                        ],
                    ],
                    [
                        "status" => true,
                        "image_url" => null,
                        "redirect_link" => [
                            "link" => null,
                            "link_type" => null
                        ],
                    ],
                    [
                        "status" => true,
                        "image_url" => null,
                        "redirect_link" => [
                            "link" => null,
                            "link_type" => null
                        ],
                    ],
                    [
                        "status" => true,
                        "image_url" => null,
                        "redirect_link" => [
                            "link" => null,
                            "link_type" => null
                        ],
                    ],
                ],
            ],
            "products_ids" => null,
            "offer_products" => [
                "title" => "Offers",
                "status" => true,
                "sub_title" => "Popular Offers",
                "product_ids" => null,
            ],
            "categories_list" => [
                "title" => "Categories",
                "status" => true,
                "category_ids" => null,
            ],
            "navigate_button" => [
                "path" => "Button Text",
                "title" => "What are you looking for?",
                "status" => true,
                "button_text" => "Category",
            ],
            "section_1_products" => [
                "title" => "Section One",
                "status" => true,
                "sub_title" => "Top Save Today",
                "product_ids" => null,
            ],
            "section_2_products" => [
                "title" => "Section two",
                "status" => true,
                "sub_title" => "New Arrivals",
                "product_ids" => null,
            ],
            "section_3_products" => [
                "title" => "Section 3",
                "status" => true,
                "sub_title" => "Top Selling Items",
                "product_ids" => null,
            ]
        ];

        AppSetting::updateOrCreate(['values' => $values]);
        DB::table('seeders')->updateOrInsert([
            'name' => 'AppSettingSeeder',
            'is_completed' => true
        ]);
    }
}
