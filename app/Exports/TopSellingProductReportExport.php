<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromQuery;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class TopSellingProductReportExport implements FromQuery, WithMapping, WithHeadings, ShouldQueue
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function query()
    {
        $products = Product::withCount('orders')->orderByDesc('orders_count');
        return $this->filter($products, request());
    }

    public function columns(): array
    {
        return ["id","name", "order_count", "status", "created_at"];
    }

    public function map($product): array
    {
        return [
            $product->id,
            $product->name,
            $product->orders_count,
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
        if ($request->start_date && $request->end_date) {
            $products = $products->whereHas('orders', function ($query) use ($request) {
                $query->whereBetween('created_at', [$request->start_date, $request->end_date]);
            });
        }

        return $products;
    }
}
