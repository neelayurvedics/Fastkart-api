<?php

return [
    'data' => [
        [
            'title' => 'Coupon Report',
            'field_type' => 'coupon',
            'show_fields_table' => ['code' ,'type', 'amount', 'usage_count', 'exclude_products_count', 'usage_sum_coupon_total_discount'],
            'fields' => [
                [
                    'type' => 'text',
                    'label' => 'Search',
                    'name' => 'search',
                    'placeholder' => 'Enter Here...',
                ],
                [
                    'type' => 'select',
                    'label' => 'Status',
                    'name' => 'status',
                    'placeholder' => 'Select Status',
                    'options' => [
                        [
                            'value' => 1,
                            'label' => 'Active',
                        ],
                        [
                            'value' => 0,
                            'label' => 'Deactive',
                        ],
                    ],
                ],
                [
                    'type' => 'date',
                    'label' => 'Start Date',
                    'name' => 'start_date',
                    'placeholder' => 'Start Date'
                ],
                [
                    'type' => 'date',
                    'label' => 'End Date',
                    'name' => 'end_date',
                    'placeholder' => 'End Date'
                ],
            ],
        ],
        [
            'title' => 'Product Sale Report',
            'field_type' => 'product-sale',
            'show_fields_table' => ['name', 'sale_price', 'orders_count', 'store.store_name'],
            'fields' => [
                [
                    'type' => 'text',
                    'label' => 'Search',
                    'name' => 'search',
                    'placeholder' => 'Enter Here...',
                ],
                [
                    'type' => 'select',
                    'name' => 'status',
                    'label' => 'Status',
                    'placeholder' => 'Select Status',
                    'options' => [
                        [
                            'value' => 1,
                            'label' => 'Active',
                        ],
                        [
                            'value' => 0,
                            'label' => 'Deactive',
                        ],
                    ],
                ],
                [
                    'type' => 'select',
                    'name' => 'product_type',
                    'label' => 'Product Type',
                    'placeholder' => 'Select Product Type',
                    'options' => [
                        [
                            'value' => 'physical',
                            'label' => 'physical',
                        ],
                        [
                            'value' => 'digital',
                            'label' => 'digital',
                        ],
                        [
                            'value' => 'external',
                            'label' => 'external',
                        ],
                    ],
                ],
                [
                    'type' => 'date',
                    'label' => 'Start Date',
                    'name' => 'start_date',
                    'placeholder' => 'Start Date',
                ],
                [
                    'type' => 'date',
                    'label' => 'End Date',
                    'name' => 'end_date',
                    'placeholder' => 'End Date'
                ],
            ],
        ],
        [
            'title' => 'Vendor Product Sale Report',
            'field_type' => 'vendor-product-sale',
            'show_fields_table' => ['store_name', 'orders_count', 'products_count'],
            'fields' => [
                [
                    'type' => 'text',
                    'label' => 'Search',
                    'name' => 'search',
                    'placeholder' => 'Enter Here...',
                ],
                [
                    'type' => 'select',
                    'name' => 'status',
                    'label' => 'Status',
                    'placeholder' => 'Select Status',
                    'options' => [
                        [
                            'value' => 1,
                            'label' => 'Active',
                        ],
                        [
                            'value' => 0,
                            'label' => 'Deactive',
                        ],
                    ],
                ],
                [
                    'type' => 'select',
                    'name' => 'status',
                    'label' => 'Product Type',
                    'placeholder' => 'Select Product Type',
                    'options' => [
                        [
                            'value' => 'physical',
                            'label' => 'physical',
                        ],
                        [
                            'value' => 'digital',
                            'label' => 'digital',
                        ],
                        [
                            'value' => 'external',
                            'label' => 'external',
                        ],
                    ],
                ],
                [
                    'type' => 'date',
                    'label' => 'Start Date',
                    'name' => 'start_date',
                    'placeholder' => 'Start Date'
                ],
                [
                    'type' => 'date',
                    'label' => 'End Date',
                    'name' => 'end_date',
                    'placeholder' => 'End Date'
                ],
            ],
        ],
        [
            'title' => 'Wishlist Report',
            'field_type' => 'wishlist',
            'show_fields_table' => ['name', 'wishlist_count'],
            'fields' => [
                [
                    'type' => 'text',
                    'label' => 'Search',
                    'name' => 'search',
                    'placeholder' => 'Enter Here...',
                ],
                [
                    'type' => 'date',
                    'label' => 'Start Date',
                    'name' => 'start_date',
                    'placeholder' => 'Start Date'
                ],
                [
                    'type' => 'date',
                    'label' => 'End Date',
                    'name' => 'end_date',
                    'placeholder' => 'End Date',
                ]
            ]
        ],
        [
            'title' => 'Cart Report',
            'field_type' => 'cart',
            'show_fields_table' => ['consumer.name', 'product_count', 'total_amount'],
            'fields' => [
                [
                    'type' => 'date',
                    'label' => 'Start Date',
                    'name' => 'start_date',
                    'placeholder' => 'Start Date',
                ],
                [
                    'type' => 'date',
                    'label' => 'End Date',
                    'name' => 'end_date',
                    'placeholder' => 'End Date',
                ]
            ]
        ],
        [
            'title' => 'Product In Stock Report',
            'field_type' => 'in-stock',
            'show_fields_table' => ['name', 'stock'],
            'fields' => [
                [
                    'type' => 'text',
                    'label' => 'Search',
                    'name' => 'search',
                    'placeholder' => 'Enter Here...',
                ]
            ]
        ],
        [
            'title' => 'Product Out Of Stock Report',
            'field_type' => 'out-of-stock',
            'show_fields_table' => ['name', 'stock'],
            'fields' => [
                [
                    'type' => 'text',
                    'label' => 'Search',
                    'name' => 'search',
                    'placeholder' => 'Enter Here...',
                ]
            ]
        ],
        [
            'title' => 'Category Report',
            'field_type' => 'category-sale',
            'show_fields_table' => ['name','orders_count','products_count'],
            'fields' => [
                [
                    'type' => 'text',
                    'label' => 'Search',
                    'name' => 'search',
                    'placeholder' => 'Enter Here...',
                ],
                [
                    'type' => 'date',
                    'label' => 'Start Date',
                    'name' => 'start_date',
                    'placeholder' => 'Start Date',
                ],
                [
                    'type' => 'date',
                    'label' => 'End Date',
                    'name' => 'end_date',
                    'placeholder' => 'End Date',
                ]
            ]
        ],
        [
            'title' => 'Payment Gateway Report',
            'field_type' => 'payment-gateways',
            'show_fields_table' => ['payment_method','total_orders','total_amount'],
            'fields' => [
                [
                    'type' => 'text',
                    'label' => 'Search',
                    'name' => 'search',
                    'placeholder' => 'Enter Here...',
                ],
                [
                    'type' => 'date',
                    'label' => 'Start Date',
                    'name' => 'start_date',
                    'placeholder' => 'Start Date',
                ],
                [
                    'type' => 'date',
                    'label' => 'End Date',
                    'name' => 'end_date',
                    'placeholder' => 'End Date',
                ]
            ]
        ],
        [
            'title' => 'Tax Report',
            'field_type' => 'tax',
            'show_fields_table' => ['country', 'total_tax', 'total_orders', 'total_order_amount'],
            'fields' => [
                [
                    'type' => 'date',
                    'label' => 'Start Date',
                    'name' => 'start_date',
                    'placeholder' => 'Start Date',
                ],
                [
                    'type' => 'date',
                    'label' => 'End Date',
                    'name' => 'end_date',
                    'placeholder' => 'End Date',
                ]
            ]
        ]
    ],
];
