<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class ProductSaleExport implements FromQuery, WithMapping, WithHeadings, ShouldQueue
{

    use Exportable;

    /**
    * @return \Illuminate\Support\Collection
    */
    public function query()
    {
        $products = Product::with(['orders', 'store', 'categories',])
                    ->select('id', 'name', 'price', 'sale_price', 'discount', 'store_id', 'status', 'created_at')
                    ->whereNull('deleted_at')->latest('created_at');
        return $this->filter($products, request());
    }

    public function columns(): array
    {
        return ["id", "name", "num_of_sales", "vendor", "price", "sale_price", "discount", "order_count", "status", "created_at"];
    }

    public function map($product): array
    {
        return [
            $product->id,
            $product->name,
            $product->orders()->count(),
            $product->store->vendor->name,
            $product->price,
            $product->sale_price,
            $product->discount,
            $product->order_count,
            $product->status,
            $product->created_at,
        ];
    }

    public function headings(): array
    {
        return $this->columns();
    }

    public function filter($products, $request)
    {
        if ($request->category_ids) {
            $category_ids = explode(',', $request->category_ids);
            $products = $products->whereRelation('categories', function($categories) use($category_ids) {
                $categories->WhereIn('category_id', $category_ids);
            });
        }

        if ($request->product_type) {
            $products = $products->where('product_type', $request->product_type);
        }

        if ($request->store_ids) {
            $store_ids = explode(',', $request->store_ids);
            $products = $products->whereIn('store_id', $store_ids);
        }

        if ($request->search) {
            $searchTerm = $request->search;
            $products = $products->where(function ($query) use ($searchTerm) {
                $query->where('name', 'like', '%' . $searchTerm . '%')
                    ->orWhereHas('store', function ($query) use ($searchTerm) {
                        $query->whereHas('vendor', function ($query) use ($searchTerm) {
                            $query->where('name', 'like', '%' . $searchTerm . '%');
                        });
                    });
            });
        }

        if ($request->start_date && $request->end_date) {
            $startDate = $request->start_date;
            $endDate = $request->end_date;

            $products = $products->whereHas('orders', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('orders.created_at', [$startDate, $endDate]);
            });
        }

        return $products;
    }

}
