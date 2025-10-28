<?php

namespace App\Repositories\Eloquents;

use Exception;
use Carbon\Carbon;
use App\Models\Order;
use App\Models\Point;
use App\Helpers\Helpers;
use App\Enums\WalletPointsDetail;
use Illuminate\Support\Facades\DB;
use App\Http\Traits\WalletPointsTrait;
use App\GraphQL\Exceptions\ExceptionHandler;
use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;

class PointsRepository extends BaseRepository
{
    use WalletPointsTrait;

    protected $fieldSearchable = [
        'transactions.type' =>'like',
        'transactions.detail' =>'like',
    ];

    public function boot()
    {
        try {

            $this->pushCriteria(app(RequestCriteria::class));

        } catch (ExceptionHandler $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    function model()
    {
        return Point::class;
    }

    public function credit($request)
    {
        try {

            $points = $this->creditPoints($request->consumer_id, $request->balance, WalletPointsDetail::ADMIN_CREDIT);
            if ($points) {
                $points->setRelation('transactions', $points->transactions()
                    ->paginate($request->paginate ?? $points->transactions()->count()));
            }

            return $points;

        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function debit($request)
    {
        try {

            $points = $this->debitPoints($request->consumer_id, $request->balance, WalletPointsDetail::ADMIN_DEBIT);
            if ($points) {
                $points->setRelation('transactions', $points->transactions()
                    ->paginate($request->paginate ?? $points->transactions()->count()));
            }

            return $points;

        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function getDeliveredDays($order)
    {
        return now()->diffInDays(Carbon::parse($order->delivered_at)->toDateString());
    }

    public function verifyDeliveryDays($order, $refundableDays)
    {
        if ($this->getDeliveredDays($order) <= $refundableDays) {
            return true;
        }

        return false;
    }

    public function rewardPoints()
    {
        DB::beginTransaction();
        try {

            if (Helpers::pointIsEnable()) {
                $orderIds = $this->getTransactionOrders();
                $orders = Order::whereNull('deleted_at')?->whereNull('parent_id')?->whereNotIn('id', $orderIds)->get();
                $settings = Helpers::getSettings();
                foreach($orders as $order) {
                    if ($this->verifyDeliveryDays($order, $settings['refund']['refundable_days'])) {
                        $rewardPoints = $this->getRewardPoints($order->total);
                        if ($rewardPoints) {
                            $this->creditPoints($order->consumer_id, $rewardPoints, WalletPointsDetail::REWARD . ' #' . $order->order_number, $order->id);
                        }
                    }
                }
            }

            DB::commit();
            return true;

        } catch (Exception $e) {

            DB::rollback();
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }
}
