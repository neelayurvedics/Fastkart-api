<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\Eloquents\ReportRepository;

class ReportController extends Controller
{
    protected $repository;

    public function __construct(ReportRepository $repository){
        $this->repository = $repository;
    }

    public function index()
    {
        //
    }

    public function getReports()
    {
        return $this->repository->getReports();
    }

    public function getFields(Request $request)
    {
        return $this->repository->getFields($request);
    }

    public function getReportFields(Request $request)
    {
        return $this->repository->getReportFields($request);
    }

    public function couponReport(Request $request)
    {
        return $this->repository->couponReport($request);
    }

    public function exportCouponReport(Request $request)
    {
        return $this->repository->exportCouponReport($request);
    }

    public function productSaleReport(Request $request)
    {
        return $this->repository->productSaleReport($request);
    }

    public function exportProductSaleReport(Request $request)
    {
        return $this->repository->exportProductSaleReport($request);
    }

    public function vendorProductSaleReport(Request $request)
    {
        return $this->repository->vendorProductSaleReport($request);
    }

    public function exportVendorProductSaleReport(Request $request)
    {
        return $this->repository->exportVendorProductSaleReport($request);
    }

    public function wishlistReport(Request $request)
    {
        return $this->repository->wishlistReport($request);
    }

    public function exportWishlistReport(Request $request)
    {
        return $this->repository->exportWishlistReport($request);

    }

    public function highestVendorCommissions(Request $request)
    {
        return $this->repository->highestVendorCommissions($request);
    }

    public function cartReport(Request $request)
    {
        return $this->repository->cartReport($request);
    }

    public function exportCartReport(Request $request)
    {
        return $this->repository->exportCartReport($request);
    }

    public function productInStockReport(Request $request)
    {
        return $this->repository->productInStockReport($request);
    }

    public function exportProductInStockReport(Request $request)
    {
        return $this->repository->exportProductInStockReport($request);
    }

    public function productOutOfStockReport(Request $request)
    {
        return $this->repository->productOutOfStockReport($request);
    }

    public function exportProductOutOfStockReport(Request $request)
    {
        return $this->repository->exportProductOutOfStockReport($request);
    }

    public function categorySaleReport(Request $request)
    {
        return $this->repository->categorySaleReport($request);
    }

    public function exportCategorySaleReport(Request $request)
    {
        return $this->repository->exportCategorySaleReport($request);
    }

    public function topSellingProducts(Request $request)
    {
        return $this->repository->topSellingProducts($request);
    }

    public function exportTopSellingProducts(Request $request)
    {
        return $this->repository->exportTopSellingProducts($request);
    }

    public function topCustomersByOrders(Request $request)
    {
        return $this->repository->topCustomersByOrders($request);
    }

    public function topBrandsByOrders(Request $request)
    {
        return $this->repository->topBrandsByOrders($request);
    }

    public function paymentGatewaysReport(Request $request)
    {
        return $this->repository->paymentGatewaysReport($request);
    }

    public function exportPaymentGatewaysReport(Request $request)
    {
        return $this->repository->exportPaymentGatewaysReport($request);
    }

    public function transactionReport(Request $request)
    {
        return $this->repository->transactionReport($request);
    }

    public function exportTransactionReport(Request $request)
    {
        return $this->repository->exportTransactionReport($request);
    }

    public function taxReport(Request $request)
    {
        return $this->repository->taxReport($request);
    }
}
