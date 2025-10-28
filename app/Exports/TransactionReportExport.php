<?php

namespace App\Exports;

use App\Models\Transaction;
use Maatwebsite\Excel\Concerns\FromQuery;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TransactionReportExport implements FromQuery, WithMapping, WithHeadings, ShouldQueue
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function query()
    {
        $transactions = Transaction::with('order');
        return $this->filter($transactions, request());
    }

    public function columns(): array
    {
        return ["id", "order_number", "payment_method", "consumer", "Payment_status", "order_amount"];
    }

    public function map($transaction): array
    {
        return [
            $transaction->id,
            $transaction->order_number,
            $transaction->payment_method,
            $transaction->consumer,
            $transaction->payment_status,
            $transaction->order_amount,
        ];
    }

    /**
     * Define the headings for the Excel file.
     *
     * @return array
     */
    public function headings(): array
    {
        return $this->columns();
    }

    public function filter($transactions, $request)
    {
        if ($request->start_date && $request->end_date) {
            $transactions = $transactions->whereBetween('created_at', [$request->start_date, $request->end_date]);
        }

        if ($request->search) {
            $transactions = $transactions->where('payment_method', 'like', '%' . $request->search . '%');
        }

        return $transactions;
    }
}
