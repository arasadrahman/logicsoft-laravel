<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\PdfService;
use App\Services\ExcelService;

class InvoiceWiseGrossProfitLossController extends Controller
{
    public function index(Request $request)
    {
        $pageTitle = "Invoice Wise Gross Profit / Loss";

        [$shopId, $startDate, $endDate] = $this->filters($request);

        $shops = $this->shops();
        $dataset = $this->dataset($shopId, $startDate, $endDate);

        return view(
            "reports.invoice-wise-gross-profit-loss",
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
            ->make("reports.pdf.invoice-wise-gross-profit-loss", array_merge(
                [
                    "shopId" => $shopId,
                    "startDate" => $startDate,
                    "endDate" => $endDate,
                ],
                $dataset
            ))
            ->fileName("Invoice-Wise-Gross-Profit-Loss.pdf")
            ->paper("A4", "portait")
            ->stream();
    }

    public function excel(Request $request, ExcelService $excelService)
    {
        [$shopId, $startDate, $endDate] = $this->filters($request);

        $dataset = $this->dataset($shopId, $startDate, $endDate, 'ASC');

        $sheetTitle = "Invoice Wise Gross Profit Loss {$startDate} to {$endDate}";
        $fileName = "invoice_wise_gross_profit_loss_{$startDate}_{$endDate}.xlsx";

        return $excelService->export(
            view("reports.excel.invoice-wise-gross-profit-loss", array_merge(
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

        return [
            "rows" => $rows,
            "total" => $rows->count(),
            "totalQty" => $rows->sum("Qty"),
            "totalCostAmount" => $rows->sum("CostAmount"),
            "totalSaleAmount" => $rows->sum("SaleAmount"),
            "totalGrossAmount" => $rows->sum("GPAmt"),
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

        $query = DB::table("saleinvoice")
            ->selectRaw("
                Invoice,
                SUM(Qty)        AS Qty,
                SUM(CostAmount) AS CostAmount,
                SUM(SaleAmount) AS SaleAmount,
                SUM(GPAmt)      AS GPAmt,
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
                "ShopID",
                "UserID",
                "SaleDT",
                "SaleTM",
                "CounterID"
            )
            ->orderBy("ShopID")
            ->orderBy("Invoice", $orderBy);

        if ($shopId) {
            $query->where("ShopID", $shopId);
        }

        return $query->get();
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
