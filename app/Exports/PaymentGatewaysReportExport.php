<?php

namespace App\Exports;

use App\Models\Order;
use Maatwebsite\Excel\Concerns\FromQuery;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class PaymentGatewaysReportExport implements FromQuery, WithMapping, WithHeadings, ShouldQueue
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function query()
    {
        $payments = Order::selectRaw('payment_method, COUNT(*) as total_orders, SUM(amount) as total_amount')
            ->groupBy('payment_method')
            ->orderByDesc('total_orders');

        return $this->filter($payments, request());
    }

    public function columns(): array
    {
        return ["payment_method","total_orders", "total_amount", "order_status"];
    }

    public function map($payments): array
    {
        return [
            $payments->payment_method,
            $payments->total_orders,
            $payments->total_amount,
            $payments->order_status
        ];
    }

    public function headings(): array
    {
        return $this->columns();
    }

    public function filter($payments, $request)
    {
        if ($request->start_date && $request->end_date) {
            $startDate = $request->start_date;
            $endDate = $request->end_date;

            $payments = $payments->whereBetween('created_at', [$startDate, $endDate]);
        }

        if ($request->search) {
            $payments = $payments->where('payment_method', 'like', '%' . $request->search . '%');
        }

        return $payments;
    }
}
