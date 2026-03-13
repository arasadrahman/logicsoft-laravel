<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\PdfService;
use App\Services\ExcelService;

class SalesDetailsController extends Controller
{
    public function index(Request $request)
    {
        $pageTitle = "Daily Sales Details";

        [$shopId, $startDate, $endDate] = $this->filters($request);

        $shops = $this->shops();
        $rows = $this->reportData($shopId, $startDate, $endDate);

        $summary = (object)[
            "totalQuantity" => $rows->sum("Qty"),
            "totalAmount" => $rows->sum("SaleAmount"),
        ];

        $groupedData = $rows->groupBy("Catagory");

        return view(
            "reports.sales-details",
            compact(
                "pageTitle",
                "groupedData",
                "summary",
                "shops",
                "shopId",
                "startDate",
                "endDate"
            )
        );
    }

    public function pdf(Request $request, PdfService $pdf)
    {
        [$shopId, $startDate, $endDate] = $this->filters($request);

        $rows = $this->reportData($shopId, $startDate, $endDate, 'ASC');

        $summary = (object)[
            "totalItems" => $rows->count("Item"),
            "totalQuantity" => $rows->sum("Qty"),
            "totalNetAmt" => $rows->sum("SaleAmount"),
            "totalDiscAmt" => $rows->sum("DiscAmt"),
            "totalGrsAmt" => $rows->sum("GrsAmt")
        ];

        return $pdf
            ->make("reports.pdf.sales-details", [
                "rows" => $rows->groupBy("Catagory"),
                "shopId" => $shopId,
                "startDate" => $startDate,
                "endDate" => $endDate,
                "summary" => $summary,
            ])
            ->fileName("Daily-Sales-Details.pdf")
            ->paper("A4", "landscape")
            ->stream();
    }

    public function excel(Request $request, ExcelService $excelService)
    {
        [$shopId, $startDate, $endDate] = $this->filters($request);

        $rows = $this->reportData($shopId, $startDate, $endDate, 'ASC');

        $summary = (object)[
            "totalItems" => $rows->count("Item"),
            "totalQuantity" => $rows->sum("Qty"),
            "totalNetAmt" => $rows->sum("SaleAmount"),
            "totalDiscAmt" => $rows->sum("DiscAmt"),
            "totalGrsAmt" => $rows->sum("GrsAmt")
        ];

        $sheetTitle = "Sales Details {$startDate} to {$endDate}";
        $fileName = "sales_details_{$startDate}_{$endDate}.xlsx";

        return $excelService->export(
            view("reports.excel.sales-details", [
                "rows" => $rows->groupBy("Catagory"),
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

        $query = DB::table("saleitem")
            ->selectRaw("
                Catagory,
                Item,
                Barcode,
                MRP,
                SUM(Qty) as Qty,
                SUM(SaleAmount) as SaleAmount,
                SUM(DiscAmt) as DiscAmt,
                SUM(GrsAmt) as GrsAmt
            ")
            ->where("ShopPrefix", $shopPrefix)
            ->whereBetween("SaleDT", [$startDate, $endDate])
            ->groupBy("Catagory", "Item", "Barcode", "MRP")
            ->orderBy("Catagory")
            ->orderBy("GrsAmt", $orderDirection);

        if ($shopId) {
            $query->where("ShopID", $shopId);
        }

        return $query->get();
    }

    private function shops()
    {
        $shopPrefix = auth()->user()->ShopPrefix;

        return DB::table("saleitem")
            ->select("ShopID")
            ->where("ShopPrefix", $shopPrefix)
            ->distinct()
            ->orderBy("ShopID")
            ->get();
    }
}
