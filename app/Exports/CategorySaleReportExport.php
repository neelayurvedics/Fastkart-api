<?php

namespace App\Exports;

use App\Models\Category;
use Maatwebsite\Excel\Concerns\FromQuery;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class CategorySaleReportExport implements FromQuery, WithMapping, WithHeadings, ShouldQueue
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function query()
    {
        $categories = Category::with(['products.orders'])->where('type', 'product');
        return $categories;
    }

    public function columns(): array
    {
        return ["id", "name", "products_count", "orders_count"];
    }

    public function map($category): array
    {
        return [
            $category->id,
            $category->name,
            $category->products_count,
            $category->orders_count
        ];
    }

    public function headings(): array
    {
        return $this->columns();
    }
}
