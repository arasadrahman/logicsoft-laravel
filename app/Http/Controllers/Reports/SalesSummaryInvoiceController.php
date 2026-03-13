<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\PdfService;
use App\Services\ExcelService;

class SalesSummaryInvoiceController extends Controller
{
    public function index(Request $request)
    {
        $pageTitle = "Invoice Wise Sales Summary";

        [$shopId, $startDate, $endDate] = $this->filters($request);

        $shops = $this->shops();
        $data = $this->reportData($shopId, $startDate, $endDate);

        $summary = (object)[
            "totalInvoice" => $data->unique("Invoice")->count(),
            "totalQuantity" => $data->sum("Qty"),
            "totalAmount" => $data->sum("SaleAmount"),
        ];

        return view(
            "reports.sales-summary-invoice",
            compact(
                "pageTitle",
                "data",
                "shops",
                "shopId",
                "startDate",
                "endDate",
                "summary"
            )
        );
    }

    public function pdf(Request $request, PdfService $pdf)
    {
        [$shopId, $startDate, $endDate] = $this->filters($request);

        $data = $this->reportData($shopId, $startDate, $endDate, 'ASC');
        $groupedData = $this->groupByShop($data);


        $summary = (object)[
            "totalInvoice" => $data->unique("Invoice")->count(),
            "totalQuantity" => $data->sum("Qty"),
            "totalAmount" => $data->sum("SaleAmount"),
        ];

        return $pdf
            ->make("reports.pdf.sales-summary-invoice", [
                "data" => $data,
                "groupedData" => $groupedData,
                "shopId" => $shopId,
                "startDate" => $startDate,
                "endDate" => $endDate,
                "summary" => $summary,
            ])
            ->fileName("Sales-Summary-Invoice.pdf")
            ->paper("A4", "portrait")
            ->stream();
    }

    public function excel(Request $request, ExcelService $excelService)
    {
        [$shopId, $startDate, $endDate] = $this->filters($request);

        $rows = $this->reportData($shopId, $startDate, $endDate, 'ASC');
        $groupedData = $this->groupByShop($rows);

        $summary = (object)[
            "totalInvoice" => $rows->unique("Invoice")->count(),
            "totalQuantity" => $rows->sum("Qty"),
            "totalAmount" => $rows->sum("SaleAmount"),
        ];

        $sheetTitle = "Sales Summary {$startDate} to {$endDate}";
        $fileName = "sales_summary_invoice_{$startDate}_{$endDate}.xlsx";

        return $excelService->export(
            view("reports.excel.sales-summary-invoice", [
                "rows" => $rows,
                "groupedData" => $groupedData,
                "shopId" => $shopId,
                "startDate" => $startDate,
                "endDate" => $endDate,
                "summary" => $summary,
            ]),
            $fileName,
            $sheetTitle
        );
    }

    private function filters(Request $request): array
    {
        return [
            $request->shopId,
            $request->startDate ?? now()->toDateString(),
            $request->endDate ?? now()->toDateString(),
        ];
    }

    private function reportData($shopId, $startDate, $endDate, $orderDirection = 'DESC')
    {
        $shopPrefix = auth()->user()->ShopPrefix;

        $query = DB::table("saleinvoice")
            ->selectRaw("
                Invoice,
                SUM(Qty) as Qty,
                SUM(SaleAmount) as SaleAmount,
                PayType,
                ShopID,
                UserID,
                SaleDT,
                SaleTM,
                CounterID
            ")
            ->where("ShopPrefix", $shopPrefix)
            ->whereBetween("SaleDT", [$startDate, $endDate])
            ->groupBy(
                "Invoice",
                "PayType",
                "ShopID",
                "UserID",
                "SaleDT",
                "SaleTM",
                "CounterID"
            )
            ->orderBy("ShopID")
            ->orderBy("Invoice", $orderDirection);

        if ($shopId) {
            $query->where("ShopID", $shopId);
        }

        return $query->get();
    }
    
    private function groupByShop($rows)
    {
        return $rows->sortBy('ShopID')->groupBy('ShopID');
    }


    private function shops()
    {
        $shopPrefix = auth()->user()->ShopPrefix;

        return DB::table("saleinvoice")
            ->select("ShopID")
            ->where("ShopPrefix", $shopPrefix)
            ->distinct()
            ->orderBy("ShopID")
            ->get();
    }
}
