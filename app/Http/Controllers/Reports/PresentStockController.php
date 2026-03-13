<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\PdfService;
use App\Services\ExcelService;

class PresentStockController extends Controller
{
    public function index(Request $request)
    {
        $pageTitle = "Present Stock";

        [$shopId, $cat] = $this->filters($request);

        $shops = $this->shops();
        $categories = $this->categories();
        $dataset = $this->dataset($shopId, $cat);

        return view(
            "reports.present-stock",
            array_merge(
                compact(
                    "pageTitle",
                    "shops",
                    "categories",
                    "shopId",
                    "cat"
                ),
                $dataset
            )
        );
    }

    public function pdf(Request $request, PdfService $pdf)
    {
        [$shopId, $cat] = $this->filters($request);
        $dataset = $this->dataset($shopId, $cat);

        return $pdf
            ->make("reports.pdf.present-stock", array_merge(
                ["shopId" => $shopId, "cat" => $cat],
                $dataset
            ))
            ->fileName("present-stock.pdf")
            ->paper("A4", "landscape")
            ->stream();
    }

    public function excel(Request $request, ExcelService $excelService)
    {
        [$shopId, $cat] = $this->filters($request);
        $dataset = $this->dataset($shopId, $cat);

        $sheetTitle = "Present Stock Report";
        $fileName = "present_stock_" . now()->format('Y_m_d') . ".xlsx";

        return $excelService->export(
            view("reports.excel.present-stock", array_merge(
                ["shopId" => $shopId, "cat" => $cat],
                $dataset
            )),
            $fileName,
            $sheetTitle
        );
    }

    private function dataset($shopId, $cat): array
    {
        $rows = $this->reportData($shopId, $cat);

        $TQty = $rows->sum("TQty");
        $TCP = $rows->sum("TCP");
        $TSP = $rows->sum("TSP");

        $firstRow = $rows->first();
        $StockDT = $firstRow ? $firstRow->StockDT . " " . $firstRow->StockTM : "";

        $stock_pname = $rows->map(fn($r) => $r->PrdName . " - " . $r->GroupName)->values();
        $stock_qty = $rows->pluck("TQty")->values();

        return [
            "rows" => $rows,
            "TQty" => $TQty,
            "TCP" => $TCP,
            "TSP" => $TSP,
            "StockDT" => $StockDT,
            "stock_pname" => $stock_pname,
            "stock_qty" => $stock_qty,
        ];
    }

    private function filters(Request $request): array
    {
        return [
            $request->shopId,
            $request->cat,
        ];
    }

    private function reportData($shopId, $cat)
    {
        $shopPrefix = auth()->user()->ShopPrefix;

        $query = DB::table("stock")
            ->selectRaw('
                MAX(StockDT) AS StockDT,
                MAX(StockTM) AS StockTM,
                MAX(ShopID)  AS ShopID,
                GroupName,
                PrdName,
                SUM(TQty) AS TQty,
                SUM(TCP)  AS TCP,
                SUM(TSP)  AS TSP
            ')
            ->where("ShopPrefix", $shopPrefix)
            ->groupBy("GroupName", "PrdName")
            ->orderBy("GroupName")
            ->orderBy("PrdName");

        if ($shopId) {
            $query->where("ShopID", $shopId);
        }

        if ($cat) {
            $query->where("GroupName", $cat);
        }

        return $query->get();
    }

    private function shops()
    {
        return DB::table("stock")
            ->select("ShopID")
            ->where("ShopPrefix", auth()->user()->ShopPrefix)
            ->distinct()
            ->orderBy("ShopID")
            ->get();
    }

    private function categories()
    {
        return DB::table("stock")
            ->select("GroupName")
            ->where("ShopPrefix", auth()->user()->ShopPrefix)
            ->distinct()
            ->orderBy("GroupName")
            ->get();
    }
}