<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class VendorProductSaleExport implements FromQuery, WithMapping, WithHeadings, ShouldQueue
{
    use Exportable;

    /**
    * @return \Illuminate\Support\Collection
    */
    public function query()
    {
        $vendors = User::with(['store.orders'])
                    ->whereHas('roles', function ($query) {
                        $query->where('name', 'vendor');
                    })->whereNull('deleted_at')->latest('created_at');
        return $this->filter($vendors, request());
    }

    public function columns(): array
    {
        return ["id", "vendor_name","store_name","product_sales","order_amount", "status", "created_at"];
    }

    public function map($vendor): array
    {
        return [
            $vendor->id,
            $vendor->name,
            $vendor->store->store_name,
            $vendor->store->orders->count(),
            $vendor->store->orders->sum('total_amount'),
            $vendor->status,
            $vendor->created_at,
        ];
    }

    public function headings(): array
    {
        return $this->columns();
    }

    public function filter($vendors, $request)
    {
        if ($request->vendor_id) {
            $vendors = $vendors->where('id', $request->vendor_id);
        }

        if ($request->store_id) {
            $vendors = $vendors->whereHas('store', function ($query) use ($request) {
                $query->where('id', $request->store_id);
            });
        }

        if ($request->search) {
            $searchTerm = $request->search;
            $vendors = $vendors->where(function ($query) use ($searchTerm) {
                $query->where('name', 'like', '%' . $searchTerm . '%')
                    ->orWhereHas('store', function ($query) use ($searchTerm) {
                        $query->where('store_name', 'like', '%' . $searchTerm . '%');
                    });
            });
        }

        if ($request->start_date && $request->end_date) {
            $vendors = $vendors->whereHas('store.orders', function ($query) use ($request) {
                $query->whereBetween('created_at', [
                    $request->start_date,
                    $request->end_date
                ]);
            });
        }

        return $vendors;
    }

}
