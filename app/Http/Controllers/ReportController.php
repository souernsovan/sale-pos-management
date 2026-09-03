<?php

namespace App\Http\Controllers;

use App\Exports\BestSellersExport;
use App\Exports\ProfitReportExport;
use App\Exports\SalesReportExport;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        [$groupBy, $dateFrom, $dateTo] = $this->filters($request);

        $salesReport = Sale::salesGroupedBy($groupBy, $dateFrom, $dateTo);
        $profitReport = SaleItem::profitByProduct($dateFrom, $dateTo);
        $bestSellers = SaleItem::bestSellers($dateFrom, $dateTo, limit: 20);

        return view('reports.index', compact('salesReport', 'profitReport', 'bestSellers', 'groupBy', 'dateFrom', 'dateTo'));
    }

    public function exportSales(Request $request)
    {
        [$groupBy, $dateFrom, $dateTo] = $this->filters($request);

        return Excel::download(new SalesReportExport($groupBy, $dateFrom, $dateTo), 'sales-report.xlsx');
    }

    public function exportSalesPdf(Request $request)
    {
        [$groupBy, $dateFrom, $dateTo] = $this->filters($request);
        $salesReport = Sale::salesGroupedBy($groupBy, $dateFrom, $dateTo);
        $shopName = Setting::get('shop_name', config('app.name'));

        return Pdf::loadView('reports.pdf.sales', compact('salesReport', 'dateFrom', 'dateTo', 'shopName'))->stream('sales-report.pdf');
    }

    public function exportProfit(Request $request)
    {
        [, $dateFrom, $dateTo] = $this->filters($request);

        return Excel::download(new ProfitReportExport($dateFrom, $dateTo), 'profit-report.xlsx');
    }

    public function exportProfitPdf(Request $request)
    {
        [, $dateFrom, $dateTo] = $this->filters($request);
        $profitReport = SaleItem::profitByProduct($dateFrom, $dateTo);
        $shopName = Setting::get('shop_name', config('app.name'));

        return Pdf::loadView('reports.pdf.profit', compact('profitReport', 'dateFrom', 'dateTo', 'shopName'))->stream('profit-report.pdf');
    }

    public function exportBestSellers(Request $request)
    {
        [, $dateFrom, $dateTo] = $this->filters($request);

        return Excel::download(new BestSellersExport($dateFrom, $dateTo), 'best-sellers.xlsx');
    }

    public function exportBestSellersPdf(Request $request)
    {
        [, $dateFrom, $dateTo] = $this->filters($request);
        $bestSellers = SaleItem::bestSellers($dateFrom, $dateTo);
        $shopName = Setting::get('shop_name', config('app.name'));

        return Pdf::loadView('reports.pdf.best-sellers', compact('bestSellers', 'dateFrom', 'dateTo', 'shopName'))->stream('best-sellers.pdf');
    }

    private function filters(Request $request): array
    {
        return [
            $request->get('group_by', 'daily'),
            $request->get('date_from'),
            $request->get('date_to'),
        ];
    }
}
