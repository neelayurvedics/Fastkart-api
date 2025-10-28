<?php

namespace App\Repositories\Eloquents;

use Exception;
use App\Models\User;
use App\Models\Cart;
use App\Models\Brand;
use App\Models\Store;
use App\Models\Order;
use App\Models\Coupon;
use App\Models\Report;
use App\Models\Product;
use App\Enums\RoleEnum;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\CommissionHistory;
use App\Exports\CartReportExport;
use Illuminate\Support\Facades\DB;
use App\Exports\ProductSaleExport;
use App\Exports\CouponReportExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\WishlistReportExport;
use App\Exports\TransactionReportExport;
use App\Exports\VendorProductSaleExport;
use App\Exports\CategorySaleReportExport;
use App\Exports\ProductInStockReportExport;
use App\GraphQL\Exceptions\ExceptionHandler;
use App\Exports\PaymentGatewaysReportExport;
use App\Exports\ProductOutOfStockReportExport;
use App\Exports\TopSellingProductReportExport;
use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;

class ReportRepository extends BaseRepository
{
    public $user;
    public $product;

    public function boot()
    {
        try {

            $this->pushCriteria(app(RequestCriteria::class));

        } catch (ExceptionHandler $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function model()
    {
        $this->user = new User();
        $this->product = new Product();
        return Report::class;
    }

    public function getReports()
    {
        $reports = config('report.data');
        $filteredReports = array_map(function ($report) {
            return [
                'title' => $report['title'],
                'field_type' => $report['field_type'],
                'show_fields_table' => $report['show_fields_table'],
            ];
        }, $reports);

        return $filteredReports;
    }

    public function getReportFields($request)
    {
        $reportConfig = config('report');
        $reportData = $reportConfig['data'];
        if ($request->report) {
            $report = collect($reportData)->firstWhere('field_type', $request->report);

            if ($report) {
                return $report;
            }

            throw new Exception(__('errors.invalid_report_field_type'), 400);
        }

        throw new Exception(__('errors.report_type_required'), 400);
    }

    public function getFields($request)
    {
        try {

            $reportConfig = config('report');
            $reportData = $reportConfig['data'];
            if ($request->field_type) {
                $report = collect($reportData)->firstWhere('field_type', $request->field_type);

                if ($report) {
                    return $report;
                }

                throw new Exception(__('errors.invalid_report_field_type'), 400);
            }

            return $reportConfig;

        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function couponReport($request)
    {
        try {

            $coupons = Coupon::select('id', 'code', 'type', 'amount', 'title', 'description')
                ->withCount('usage')
                ->withCount('exclude_products')
                ->withSum('usage', 'coupon_total_discount');

            if (isset($request->status)) {
                $coupons = $coupons->where('status', $request->status);
            }

            if ($request->search) {
                $searchTerm = $request->search;
                $coupons = $coupons->where(function ($query) use ($searchTerm) {
                    $query->where('code', 'like', '%' . $searchTerm . '%')
                        ->orWhere('type', 'like', '%' . $searchTerm . '%')
                        ->orWhere('title', 'like', '%' . $searchTerm . '%')
                        ->orWhere('description', 'like', '%' . $searchTerm . '%');
                });
            }

            if ($request->start_date && $request->end_date) {
                $coupons = $coupons->whereDate('created_at', [$request->start_date, $request->end_date]);
            }

            if ($request->field && $request->sort) {
                $coupons = $coupons->orderBy($request->field, $request->sort);
            }

            return $coupons->paginate($request?->paginate);

        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function exportCouponReport($request)
    {
        try {

            return Excel::download(new CouponReportExport, 'coupon-report.csv');

        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function productSaleReport($request)
    {
        try {
            $products = $this->product->with([
                'store:id,store_name',
                'categories',
            ])
                ->select('id', 'name', 'price', 'sale_price', 'discount', 'store_id')
                ->withCount('orders');

            if ($request->category_ids) {
                $category_ids = explode(',', $request->category_ids);
                $products = $products->whereRelation('categories', function ($categories) use ($category_ids) {
                    $categories->WhereIn('category_id', $category_ids);
                });
            }

            if ($request->product_type) {
                $products = $products->where('product_type', $request->product_type);
            }

            if ($request->store_ids) {
                $store_ids = explode(',', $request->store_ids);
                $products = $products->whereIn('store_id', $store_ids);
            }

            if ($request->search) {
                $searchTerm = $request->search;
                $products = $products->where(function ($query) use ($searchTerm) {
                    $query->where('name', 'like', '%' . $searchTerm . '%')
                        ->orWhereHas('store', function ($query) use ($searchTerm) {
                            $query->whereHas('vendor', function ($query) use ($searchTerm) {
                                $query->where('name', 'like', '%' . $searchTerm . '%');
                            });
                        });
                });
            }

            if ($request->start_date && $request->end_date) {
                $startDate = $request->start_date;
                $endDate = $request->end_date;

                $products = $products->whereHas('orders', function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('orders.created_at', [$startDate, $endDate]);
                });
            }

            return $products->paginate($request->paginate);
        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }

    }

    public function exportProductSaleReport($request)
    {
        try {

            return Excel::download(new ProductSaleExport, 'product-sale-report.csv');

        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function vendorProductSaleReport($request)
    {
        $vendors = Store::with('vendor', 'orders')->withCount('orders');

        if ($request->vendor_id) {
            $vendors = $vendors->where('id', $request->vendor_id);
        }

        if ($request->store_id) {
            $vendors = $vendors->where('id', $request->store_id);
        }

        if ($request->search) {
            $searchTerm = $request->search;

            $vendors = $vendors->where(function ($query) use ($searchTerm) {
                $query->where('store_name', 'like', '%' . $searchTerm . '%')
                    ->orWhereHas('vendor', function ($query) use ($searchTerm) {
                        $query->where('name', 'like', '%' . $searchTerm . '%');
                    });
            });
        }

        if ($request->start_date && $request->end_date) {
            $vendors = $vendors->whereHas('orders', function ($query) use ($request) {
                $query->whereBetween('created_at', [
                    $request->start_date,
                    $request->end_date,
                ]);
            });
        }

        return $vendors->paginate($request?->paginate);
    }

    public function exportVendorProductSaleReport($request)
    {
        try {

            return Excel::download(new VendorProductSaleExport, 'vendor-product-sale-report.csv');

        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function wishlistReport($request)
    {
        $wishlistReport = $this->product->select('name')->withCount('wishlist');

        if ($request->search) {
            $wishlistReport = $wishlistReport->where('name', 'like', '%' . $request->product_name . '%');
        }

        if ($request->start_date && $request->end_date) {
            $startDate = $request->start_date;
            $endDate = $request->end_date;

            $wishlistReport = $wishlistReport->whereHas('wishlist', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            });
        }

        $wishlistReport = $wishlistReport->paginate($request?->paginate);

        return $wishlistReport;
    }

    public function exportWishlistReport($request)
    {
        try {

            return Excel::download(new WishlistReportExport, 'wishlist-report.csv');

        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function highestVendorCommissions($request)
    {
        $query = CommissionHistory::groupBy('store_id')
            ->selectRaw('store_id, SUM(vendor_commission) as total_commission')
            ->orderByDesc('total_commission');

        if ($request->start_date && $request->end_date) {
            $startDate = $request->start_date;
            $endDate = $request->end_date;

            $query = $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        return $query->paginate($request?->paginate);
    }

    public function cartReport($request)
    {
        $cartData = Cart::select('consumer_id', 'product_id',
            DB::raw('COUNT(DISTINCT product_id) as product_count'),
            DB::raw('SUM(quantity) as total_quantity'),
            DB::raw('SUM(sub_total) as total_amount'))
            ->whereNull('deleted_at')->with('consumer', 'product')
            ->groupBy('consumer_id', 'product_id');

        if ($request->start_date && $request->end_date) {
            $startDate = $request->start_date;
            $endDate = $request->end_date;

            $cartData = $cartData->whereBetween('created_at', [$startDate, $endDate]);
        }

        $cartData = $cartData->groupBy('consumer_id', 'product_id');

        return $cartData->paginate($request?->paginate);
    }

    public function exportCartReport($request)
    {
        try {

            return Excel::download(new CartReportExport, 'cart-report.csv');

        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function productInStockReport($request)
    {
        $productsInStockQuery = $this->product->select('name', 'stock')->where('stock_status', 'in_stock')
            ->where('quantity', '>', 0)
            ->select('name', 'quantity as stock');

        if ($request->search) {
            $productsInStockQuery = $productsInStockQuery->where('name', 'like', '%' . $request->product_name . '%');
        }

        return $productsInStockQuery->paginate($request?->paginate);
    }

    public function exportProductInStockReport($request)
    {
        try {

            return Excel::download(new ProductInStockReportExport, 'product-report.csv');

        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function productOutOfStockReport($request)
    {
        $productsOutOfStockQuery = $this->product->where('stock_status', 'out_of_stock')
            ->select('name', 'quantity as stock');

        if ($request->search) {
            $productsOutOfStockQuery = $productsOutOfStockQuery->where('name', 'like', '%' . $request->product_name . '%');
        }

        return $productsOutOfStockQuery->paginate($request?->paginate);
    }

    public function exportProductOutOfStockReport($request)
    {
        try {

            return Excel::download(new ProductOutOfStockReportExport, 'product-report.csv');

        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function topSellingProducts($request)
    {
        $productsQuery = $this->product->withCount('orders');

        if ($request->start_date && $request->end_date) {
            $productsQuery = $productsQuery->whereHas('orders', function ($query) use ($request) {
                $query->whereBetween('created_at', [$request->start_date, $request->end_date]);
            });
        }

        return $productsQuery->paginate($request?->paginate)
            ->sortByDesc('orders_count')
            ->values();
    }

    public function exportTopSellingProducts($request)
    {
        try {

            return Excel::download(new TopSellingProductReportExport, 'product-report.csv');

        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function categorySaleReport($request)
    {
        $categoriesQuery = Category::with(['products.orders'])
            ->where('type', 'product');

        if ($request->category_ids) {
            $category_ids = explode(',', $request->category_ids);
            $categoriesQuery = $categoriesQuery->whereIn('id', $category_ids);
        }

        if ($request->search) {
            $categoriesQuery = $categoriesQuery->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->start_date && $request->end_date) {
            $categoriesQuery = $categoriesQuery->whereHas('products.orders', function ($query) use ($request) {
                $query->whereBetween('orders.created_at', [$request->start_date, $request->end_date]);
            });
        }

        $categories = $categoriesQuery->paginate($request?->paginate)
            ->map(function ($category) use ($request) {
                $category->orders_count = $category->products->sum(function ($product) use ($request) {
                    $productOrders = $product->orders;
                    if ($request->start_date && $request->end_date) {
                        $productOrders = $productOrders->whereBetween('created_at', [$request->start_date, $request->end_date]);
                    }
                    return $productOrders->count();
                });

                $category->products = $category->products->map(function ($product) use ($request) {
                    $productOrders = $product->orders;
                    if ($request->start_date && $request->end_date) {
                        $productOrders = $productOrders->whereBetween('created_at', [$request->start_date, $request->end_date]);
                    }
                    $product->orders_count = $productOrders->count();
                    return $product;
                });

                return $category;
            });

        return $categories;
    }

    public function exportCategorySaleReport($request)
    {
        try {

            return Excel::download(new CategorySaleReportExport, 'category-report.csv');

        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function topCustomersByOrders($request)
    {
        $query = $this->user->withCount(['orders' => function ($query) use ($request) {
            if ($request->start_date && $request->end_date) {
                $startDate = $request->start_date;
                $endDate = $request->end_date;

                $query->whereBetween('created_at', [$startDate, $endDate]);
            }
        }])
            ->whereHas('roles', function ($query) {
                $query->where('name', '=', RoleEnum::CONSUMER);
            })
            ->orderByDesc('orders_count');

        return $query->paginate($request?->paginate);
    }

    public function topBrandsByOrders($request)
    {
        $brands = Brand::with(['products.orders' => function ($query) use ($request) {
            if ($request->start_date && $request->end_date) {
                $startDate = $request->start_date;
                $endDate = $request->end_date;

                $query->whereBetween('created_at', [$startDate, $endDate]);
            }
        }, 'products' => function ($query) {
            $query->withCount('orders');
        }])->paginate($request?->paginate);

        $brands->each(function ($brand) {

            $brand->orders_count = $brand->products->sum('orders_count');

            $brand->products->each(function ($product) {
                $product->orders_count = $product->orders_count;
            });
        });

        $topBrands = $brands->sortByDesc('orders_count');

        return $topBrands->values();
    }

    public function paymentGatewaysReport($request)
    {
        $paymentGatewaysQuery = Order::selectRaw('payment_method, COUNT(*) as total_orders, SUM(amount) as total_amount')
            ->groupBy('payment_method');

        if ($request->start_date && $request->end_date) {
            $startDate = $request->start_date;
            $endDate = $request->end_date;

            $paymentGatewaysQuery = $paymentGatewaysQuery->whereBetween('created_at', [$startDate, $endDate]);
        }

        if ($request->search) {
            $paymentGatewaysQuery = $paymentGatewaysQuery->where('payment_method', 'like', '%' . $request->search . '%');
        }

        return $paymentGatewaysQuery->paginate($request?->paginate);
    }

    public function exportPaymentGatewaysReport($request)
    {
        try {

            return Excel::download(new PaymentGatewaysReportExport, 'payments-report.csv');

        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function transactionReport($request)
    {
        $query = Transaction::with('order', 'from');

        if ($request->start_date && $request->end_date) {
            $startDate = $request->start_date;
            $endDate = $request->end_date;

            $query = $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        return $query->paginate($request?->paginate);
    }

    public function exportTransactionReport($request)
    {
        try {

            return Excel::download(new TransactionReportExport, 'transaction-report.csv');

        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function taxReport($request)
    {
        $query = Order::query();

        if ($request->start_date && $request->end_date) {
            $startDate = $request->start_date;
            $endDate = $request->end_date;

            $query = $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        $taxReport = $query->paginate($request?->paginate);

        $taxReportData = [];

        foreach ($taxReport as $order) {
            $country = $order->billing_address['country']['name'];

            if (!isset($taxReportData[$country])) {
                $taxReportData[$country] = [
                    'total_tax' => 0,
                    'total_shipping' => 0,
                    'total_orders' => 0,
                    'amount' => 0,
                ];
            }

            $taxReportData[$country]['total_tax'] += $order->tax_total;
            $taxReportData[$country]['total_shipping'] += $order->shipping_total;
            $taxReportData[$country]['total_orders'] += 1;
            $taxReportData[$country]['amount'] += $order->total;
        }

        $formattedTaxReportData = array_map(function ($countryData, $country) {
            return [
                'country' => $country,
                'total_tax' => round($countryData['total_tax'], 2),
                'total_orders' => $countryData['total_orders'],
                'total_order_amount' => round($countryData['amount'], 2),
            ];
        }, $taxReportData, array_keys($taxReportData));

        return collect($formattedTaxReportData);
    }
}
