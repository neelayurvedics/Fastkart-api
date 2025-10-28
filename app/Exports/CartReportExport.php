<?php

namespace App\Exports;

use App\Models\Cart;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class CartReportExport implements FromQuery, WithMapping, WithHeadings, ShouldQueue
{
    use Exportable;

    /**
    * @return \Illuminate\Support\Collection
    */
    public function query()
    {
        $cart = Cart::with(['product', 'consumer'])
            ->select('consumer_id', 'product_id', DB::raw('SUM(quantity) as total_quantity'), DB::raw('SUM(sub_total) as total_amount'))
            ->groupBy('consumer_id', 'product_id');

            return $this->filter($cart, request());
    }

    public function columns(): array
    {
        return ["id","customer_name", "product_list", "product_count", "cart_total", "status", "created_at"];
    }

    public function map($cart): array
    {
        return [
            $cart->consumer_id,
            $cart->consumer->name,
            $cart->product->name,
            $cart->total_quantity,
            $cart->total_amount,
            $cart->status,
            $cart->created_at,
        ];
    }

    public function headings(): array
    {
        return $this->columns();
    }

    public function filter($cart, $request)
    {
        if ($request->start_date && $request->end_date) {
            $startDate = $request->start_date;
            $endDate = $request->end_date;

            $cart = $cart->whereBetween('created_at', [$startDate, $endDate]);
        }

        return $cart;
    }
}
