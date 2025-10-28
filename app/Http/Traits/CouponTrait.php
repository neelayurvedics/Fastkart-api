<?php

namespace App\Http\Traits;

use Exception;
use Carbon\Carbon;
use App\Models\Coupon;
use App\Helpers\Helpers;

trait CouponTrait
{

  public function updateCouponUsage($coupon_id)
  {
    return Coupon::findOrFail($coupon_id)->decrement('usage_per_coupon');
  }

  public function isValidCoupon($coupon, $amount, $consumer)
  {
    if (Helpers::couponIsEnable()) {
      if ($coupon && $this->isValidSpend($coupon, $amount)) {
        if ($this->isCouponUsable($coupon, $consumer) && $this->isNotExpired($coupon)) {
          return true;
        }
      }

      throw new Exception(__('errors.coupon_code_min_spend',['couponCode' => $coupon->code, 'minSpend' => $coupon->min_spend]), 422);
    }

    throw new Exception(__('errors.coupon_feature_disabled'), 422);
  }

  public function isCouponUsable($coupon, $consumer)
  {
    if (!$coupon->is_unlimited) {
      if ($coupon->usage_per_customer) {
        if (!$consumer) {
          throw new Exception(__('errors.login_required_coupon',['couponCode' => $coupon->code]), 422);
        }

        $countUsedPerConsumer = Helpers::getCountUsedPerConsumer($coupon->id, $consumer);
        if ($coupon->usage_per_customer <= $countUsedPerConsumer) {
            throw new Exception(__('errors.coupon_max_usage_reached',[
              'couponCode' => $coupon->code,
              'usagePerCustomer' => $coupon->usage_per_customer
            ]), 422);
        }
      }

      if ($coupon->usage_per_coupon <= 0) {
        throw new Exception(__('errors.coupon_usage_limit_per_coupon',[
          'couponCode' => $coupon->code,
          'usagePerCoupon' => $coupon->usage_per_coupon]), 422);
      }
    }

    return true;
  }

  public function isValidSpend($coupon, $amount)
  {
    return $amount >= $coupon->min_spend;
  }

  public function isNotExpired($coupon)
  {
    if ($coupon->is_expired) {
      if (!$this->isOptimumDate($coupon)) {
        throw new Exception(__('errors.coupon_validity_period',[
          'couponCode' => $coupon->code,
          'startDate' => $coupon->start_date,
          'endDate' => $coupon->end_date
        ]), 422);
      }
    }

    return true;
  }

  public function isOptimumDate($coupon)
  {
    $currentDate = Carbon::now()->format('Y-m-d');
    if (max(min($currentDate, $coupon->end_date), $coupon->start_date) == $currentDate) {
      return true;
    }

    return false;
  }

  public function isIncludeOrExclude($coupon, $product)
  {
    if ($coupon->is_apply_all) {
      if (isset($coupon->exclude_products)) {
        if (in_array($product['product_id'], array_column($coupon->exclude_products->toArray(), 'id'))) {
          return false;
        }
      }

      return true;
    }

    if (isset($coupon->products)) {
      if (in_array($product['product_id'], array_column($coupon->products->toArray(), 'id'))) {
        return true;
      }
    }

    return false;
  }

  public function fixedDiscount($subtotal, $couponAmount)
  {
    if ($subtotal >= $couponAmount && $subtotal > 0) {
      return $couponAmount;
    }

    return 0;
  }

  public function percentageDiscount($subtotal, $couponAmount)
  {
    if ($subtotal >= $couponAmount && $subtotal > 0) {
      return ($subtotal * $couponAmount) / 100;
    }

    return 0;
  }
}
