<?php

namespace App\Exports;

use App\Models\Coupon;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class CouponReportExport implements FromQuery, WithMapping, WithHeadings, ShouldQueue
{
    use Exportable;

    /**
    * @return \Illuminate\Support\Collection
    */
    public function query()
    {
        $coupons = Coupon::select('id','code', 'type', 'amount', 'status', 'created_at')
        ->withCount('usage')
        ->withCount('exclude_products')
        ->withSum('usage', 'coupon_total_discount')->whereNull('deleted_at')->latest('created_at');
        return $this->filter($coupons, request());
    }

    public function columns(): array
    {
        return ["id","code", "type", "amount", "usage_count", "exclude_products_count", "usage_sum_coupon_total_discount","status", "created_at"];
    }

    public function map($coupon): array
    {
        return [
            $coupon->id,
            $coupon->code,
            $coupon->type,
            $coupon->amount,
            $coupon->usage_count,
            $coupon->exclude_products_count,
            $coupon->usage_sum_coupon_total_discount,
            $coupon->status,
            $coupon->created_at,
        ];
    }

    public function headings(): array
    {
        return $this->columns();
    }

    public function filter($coupons, $request)
    {

        if ($request->ids) {
            $ids = explode(',', $request->ids);
            $coupons = $coupons->whereIn('id', $ids);
        }

        if (isset($request->status)) {
            $coupons = $coupons->whereStatus($request->status);
        }

        if ($request->start_date && $request->end_date) {
            $coupons = $coupons->whereBetween('created_at', [$request->start_date, $request->end_date]);
        }

        if ($request->field && $request->sort) {
            $coupons = $coupons->orderBy($request->field, $request->sort);
        }

        return $coupons;
    }
}
