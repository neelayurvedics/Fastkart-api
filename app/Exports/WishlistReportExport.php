<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class WishlistReportExport implements FromQuery, WithMapping, WithHeadings, ShouldQueue
{
    use Exportable;

    /**
    * @return \Illuminate\Support\Collection
    */
    public function query()
    {
        $wishlists = Product::select('id', 'name', 'status', 'created_at')
                            ->withCount('wishlist')
                            ->whereNull('deleted_at')
                            ->latest('created_at');

        return $this->filter($wishlists, request());
    }

    public function columns(): array
    {
        return ["id", "name", "wishlist_count",  "status", "created_at"];
    }

    public function map($wishlist): array
    {
        return [
            $wishlist->id,
            $wishlist->name,
            $wishlist->wishlist_count,
            $wishlist->status,
            $wishlist->created_at,
        ];
    }

    public function headings(): array
    {
        return $this->columns();
    }

    public function filter($wishlists, $request)
    {
        if ($request->search) {
            $wishlists = $wishlists->where('name', 'like', '%' . $request->product_name . '%');
        }

        if ($request->start_date && $request->end_date) {
            $startDate = $request->start_date;
            $endDate = $request->end_date;

            $wishlists = $wishlists->whereHas('wishlist', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            });
        }

        return $wishlists;
    }
}
