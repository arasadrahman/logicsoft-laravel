<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\PdfService;
use App\Services\ExcelService;

class GrossProfitLossController extends Controller
{
    public function index(Request $request)
    {
        $pageTitle = "Date Wise Gross Profit";

        [$shopId, $startDate, $endDate] = $this->filters($request);

        $shops = $this->shops();
        $rows = $this->reportData($shopId, $startDate, $endDate);
        $totalGPAmt = $rows->sum("GPAmt");

        return view(
            "reports.gross-profit-loss",
            compact(
                "pageTitle",
                "shops",
                "shopId",
                "startDate",
                "endDate",
                "rows",
                "totalGPAmt"
            )
        );
    }

    public function pdf(Request $request, PdfService $pdf)
    {
        [$shopId, $startDate, $endDate] = $this->filters($request);

        $rows = $this->reportData($shopId, $startDate, $endDate, 'ASC');
        $totalGPAmt = $rows->sum("GPAmt");

        return $pdf
            ->make("reports.pdf.gross-profit-loss", [
                "rows" => $rows,
                "shopId" => $shopId,
                "startDate" => $startDate,
                "endDate" => $endDate,
                "totalGPAmt" => $totalGPAmt,
            ])
            ->fileName("Date-Wise-Gross-Profit.pdf")
            ->paper("A4", "portrait")
            ->stream();
    }

public function excel(Request $request, ExcelService $excelService)
{
    [$shopId, $startDate, $endDate] = $this->filters($request);

    $rows = $this->reportData($shopId, $startDate, $endDate, 'ASC');
    $totalGPAmt = $rows->sum("GPAmt");

    $sheetTitle = "Gross Profit Loss {$startDate} to {$endDate}";
    $fileName = "gross_profit_loss_{$startDate}_{$endDate}.xlsx";

    return $excelService->export(
        view("reports.excel.gross-profit-loss", [
            "rows" => $rows,
            "shopId" => $shopId,
            "startDate" => $startDate,
            "endDate" => $endDate,
            "totalGPAmt" => $totalGPAmt,
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

        $query = DB::table("salesum")
            ->selectRaw("
                SaleDT,
                SUM(TInv)     AS Inv,
                SUM(TQty)     AS Qty,
                SUM(TCostAmt) AS CostAmt,
                SUM(TNetAmt)  AS SaleAmount,
                SUM(TGPAmt)   AS GPAmt
            ")
            ->where("ShopPrefix", $shopPrefix)
            ->whereBetween("SaleDT", [$startDate, $endDate])
            ->groupBy("SaleDT")
            ->orderBy("SaleDT", $orderBy);

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
