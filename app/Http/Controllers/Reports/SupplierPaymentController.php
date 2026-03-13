<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\PdfService;
use App\Services\ExcelService;

class SupplierPaymentController extends Controller
{
    public function index(Request $request)
    {
        $pageTitle = "Supplier Payment";

        [$shopId, $startDate, $endDate] = $this->filters($request);

        $shops = $this->shops();
        $dataset = $this->dataset($shopId, $startDate, $endDate);
        
        return view(
            "reports.supplier-payment",
            array_merge(
                compact(
                    "pageTitle",
                    "shops",
                    "shopId",
                    "startDate",
                    "endDate"
                ),
                $dataset
            )
        );
    }

    public function pdf(Request $request, PdfService $pdf)
    {
        [$shopId, $startDate, $endDate] = $this->filters($request);

        $dataset = $this->dataset($shopId, $startDate, $endDate, 'ASC');

        return $pdf
            ->make("reports.pdf.supplier-payment", array_merge(
                [
                    "shopId" => $shopId,
                    "startDate" => $startDate,
                    "endDate" => $endDate,
                ],
                $dataset
            ))
            ->fileName("supplier-payment.pdf")
            ->paper("A4", "landscape")
            ->stream();
    }

    public function excel(Request $request, ExcelService $excelService)
    {
        [$shopId, $startDate, $endDate] = $this->filters($request);

        $dataset = $this->dataset($shopId, $startDate, $endDate, 'ASC');

        $sheetTitle = "Supplier Payment {$startDate} to {$endDate}";
        $fileName = "supplier_payment_{$startDate}_{$endDate}.xlsx";

        return $excelService->export(
            view("reports.excel.supplier-payment", array_merge(
                [
                    "shopId" => $shopId,
                    "startDate" => $startDate,
                    "endDate" => $endDate,
                ],
                $dataset
            )),
            $fileName,
            $sheetTitle
        );
    }

private function dataset($shopId, $startDate, $endDate, $orderBy = 'DESC'): array
{
    $rows = $this->reportData($shopId, $startDate, $endDate, $orderBy);

    $expHeads = $rows->pluck('ExpHead')->unique()->values();

    $table = [];
    $columnTotals = array_fill_keys($expHeads->all(), 0);

    $grandTotal = 0;

    foreach ($rows as $row) {

        $date = $row->SaleDT;
        $head = $row->ExpHead;
        $amount = (float) $row->Total;

        if (!isset($table[$date])) {

            $table[$date] = array_fill_keys($expHeads->all(), 0);
            $table[$date]['_total'] = 0;
        }

        $table[$date][$head] = $amount;
        $table[$date]['_total'] += $amount;

        // тнР COLUMN TOTAL
        $columnTotals[$head] += $amount;

        $grandTotal += $amount;
    }

    /*
    |--------------------------------------------------------------------------
    | Dynamic chunk size
    |--------------------------------------------------------------------------
    */

    $chunkSize = match (true) {
        $expHeads->count() > 40 => 6,
        $expHeads->count() > 30 => 8,
        $expHeads->count() > 20 => 10,
        default => 12,
    };

    return [
        "expHeads" => $expHeads,
        "table" => $table,
        "columnTotals" => $columnTotals,
        "totalAmount" => $grandTotal,
        "chunkSize" => $chunkSize,
    ];
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

        $query = DB::table("saleexpence")
            ->selectRaw("
                SaleDT,
                ExpHead,
                SUM(Amount) AS Total
            ")
            ->where("Type", "Supplier Payment")
            ->where("ShopPrefix", $shopPrefix)
            ->whereBetween("SaleDT", [$startDate, $endDate])
            ->groupBy("SaleDT", "ExpHead")
            ->orderBy("SaleDT", $orderBy)
            ->orderBy("ExpHead", $orderBy);

        if ($shopId) {
            $query->where("ShopID", $shopId);
        }

        return $query->get();
    }

    private function shops()
    {
        $shopPrefix = auth()->user()->ShopPrefix;

        return DB::table("saleexpence")
            ->select("ShopID")
            ->where("ShopPrefix", $shopPrefix)
            ->where("Type", "Supplier Payment")
            ->distinct()
            ->orderBy("ShopID")
            ->get();
    }
}
