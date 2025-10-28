<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromQuery;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class ProductOutOfStockReportExport implements FromQuery, WithMapping, WithHeadings, ShouldQueue
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function query()
    {
        $products = Product::where('stock_status', 'out_of_stock')
            ->select('id','name', 'quantity as stock','status', 'created_at');

        return $this->filter($products, request());
    }

    public function columns(): array
    {
        return ["id", "name", "out_of_stock", "status", "created_at"];
    }

    public function map($product): array
    {
        return [
            $product->id,
            $product->name,
            $product->stock,
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
        if ($request->search) {
            $products = $products->where('name', 'like', '%' . $request->product_name . '%');
        }

        return $products;
    }
}
