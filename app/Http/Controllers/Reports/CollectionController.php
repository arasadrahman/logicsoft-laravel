<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\PdfService;
use App\Services\ExcelService;

class CollectionController extends Controller
{
    public function index(Request $request)
    {
        $pageTitle = "Daily Collection";

        [$shopId, $startDate, $endDate] = $this->filters($request);

        $shops = $this->shops();
        $rows = $this->reportData($shopId, $startDate, $endDate);

        $payTypes = $rows->pluck("PayType")->unique()->values();

        $data = [];
        foreach ($rows as $row) {
            $data[$row->SaleDT][$row->PayType] = $row->Total;
        }

        $grandTotal = $rows->sum("Total");

        return view(
            "reports.collection",
            compact(
                "pageTitle",
                "shops",
                "shopId",
                "startDate",
                "endDate",
                "payTypes",
                "data",
                "grandTotal"
            )
        );
    }

    public function pdf(Request $request, PdfService $pdf)
    {
        [$shopId, $startDate, $endDate] = $this->filters($request);

        $rows = $this->reportData($shopId, $startDate, $endDate, 'ASC');

        $payTypes = $rows->pluck("PayType")->unique()->values();

        $data = [];
        foreach ($rows as $row) {
            $data[$row->SaleDT][$row->PayType] = $row->Total;
        }

        $grandTotal = $rows->sum("Total");

        return $pdf
            ->make("reports.pdf.collection", [
                "rows" => $rows,
                "shopId" => $shopId,
                "startDate" => $startDate,
                "endDate" => $endDate,
                "payTypes" => $payTypes,
                "data" => $data,
                "grandTotal" => $grandTotal,
            ])
            ->fileName("Daily-Collection.pdf")
            ->paper("A4", "landscape")
            ->stream();
    }

    public function excel(Request $request, ExcelService $excelService)
    {
        [$shopId, $startDate, $endDate] = $this->filters($request);

        $rows = $this->reportData($shopId, $startDate, $endDate, 'ASC');

        $payTypes = $rows->pluck("PayType")->unique()->values();

        $data = [];
        foreach ($rows as $row) {
            $data[$row->SaleDT][$row->PayType] = $row->Total;
        }

        $grandTotal = $rows->sum("Total");

        $sheetTitle = "Daily Collection {$startDate} to {$endDate}";
        $fileName = "daily_collection_{$startDate}_{$endDate}.xlsx";

        return $excelService->export(
            view("reports.excel.collection", [
                "rows" => $rows,
                "shopId" => $shopId,
                "startDate" => $startDate,
                "endDate" => $endDate,
                "payTypes" => $payTypes,
                "data" => $data,
                "grandTotal" => $grandTotal,
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

    private function reportData($shopId, $startDate, $endDate, $orderBy = 'DESC')
    {
        $shopPrefix = auth()->user()->ShopPrefix;

        $query = DB::table("saletype")
            ->selectRaw("
                SaleDT,
                PayType,
                SUM(Amount) as Total
            ")
            ->where("ShopPrefix", $shopPrefix)
            ->whereBetween("SaleDT", [$startDate, $endDate])
            ->groupBy("SaleDT", "PayType")
            ->orderBy("SaleDT", $orderBy);

        if ($shopId) {
            $query->where("ShopID", $shopId);
        }

        return $query->get();
    }

    private function shops()
    {
        $shopPrefix = auth()->user()->ShopPrefix;

        return DB::table("saletype")
            ->select("ShopID")
            ->where("ShopPrefix", $shopPrefix)
            ->distinct()
            ->orderBy("ShopID")
            ->get();
    }
}
