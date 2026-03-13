<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\PdfService;
use App\Services\ExcelService;

class NetProfitLossController extends Controller
{
    public function index(Request $request)
    {
        $pageTitle = "Date Wise Net Profit & Loss";

        [$shopId, $startDate, $endDate] = $this->filters($request);

        $shops = $this->shops();
        $rows = $this->reportData($shopId, $startDate, $endDate);
        
        // Calculate Totals for the header/footer
        $totals = [
            'gpamt' => $rows->sum("gpamt"),
            'expamt' => $rows->sum("expamt"),
            'netamount' => $rows->sum("netamount")
        ];

        return view(
            "reports.net-profit-loss",
            compact("pageTitle", "shops", "shopId", "startDate", "endDate", "rows", "totals")
        );
    }

    public function pdf(Request $request, PdfService $pdf)
    {
        [$shopId, $startDate, $endDate] = $this->filters($request);
        $rows = $this->reportData($shopId, $startDate, $endDate, 'ASC');

        return $pdf
            ->make("reports.pdf.net-profit-loss", [
                "rows" => $rows,
                "shopId" => $shopId,
                "startDate" => $startDate,
                "endDate" => $endDate,
            ])
            ->fileName("Net-Profit-Loss-Report.pdf")
            ->paper("A4", "portrait")
            ->stream();
    }

    public function excel(Request $request, ExcelService $excelService)
    {
        [$shopId, $startDate, $endDate] = $this->filters($request);
        $rows = $this->reportData($shopId, $startDate, $endDate, 'ASC');

        $fileName = "net_profit_loss_{$startDate}_{$endDate}.xlsx";

        return $excelService->export(
            view("reports.excel.net-profit-loss", compact("rows", "startDate", "endDate")),
            $fileName,
            "Net Profit Loss"
        );
    }

    private function filters(Request $request): array
    {
        return [
            $request->shopId,
            $request->startDate ?? now()->startOfMonth()->toDateString(),
            $request->endDate ?? now()->toDateString(),
        ];
    }

    private function reportData($shopId, $startDate, $endDate, $orderBy = 'DESC')
    {
        $shopPrefix = auth()->user()->ShopPrefix;

        // Subquery for aggregated sales (Summed across all shops per day)
        $salesSub = DB::table("salesum")
            ->select('SaleDT')
            ->selectRaw("SUM(TGPAmt) AS TotalGP")
            ->selectRaw("SUM(TCostAmt) AS TotalCost")
            ->selectRaw("SUM(TNetAmt) AS TotalNet")
            ->where("ShopPrefix", $shopPrefix)
            ->whereBetween("SaleDT", [$startDate, $endDate])
            // If shopId is selected, filter inside the subquery
            ->when($shopId, function($q) use ($shopId) {
                return $q->where("ShopID", $shopId);
            })
            ->groupBy('SaleDT');

        // Subquery for aggregated expenses (Filtered by Type and summed per day)
        $expenseSub = DB::table("saleexpence")
            ->select('SaleDT')
            ->selectRaw("SUM(Amount) AS TotalExp")
            ->where("ShopPrefix", $shopPrefix)
            ->where("Type", "Daily Expence") // <--- Added Filter
            ->whereBetween("SaleDT", [$startDate, $endDate])
            ->when($shopId, function($q) use ($shopId) {
                return $q->where("ShopID", $shopId);
            })
            ->groupBy('SaleDT');

        // Join the subqueries on Date only
        return DB::table(DB::raw("({$salesSub->toSql()}) as s"))
            ->mergeBindings($salesSub)
            ->leftJoin(DB::raw("({$expenseSub->toSql()}) as e"), 's.SaleDT', '=', 'e.SaleDT')
            ->mergeBindings($expenseSub)
            ->select([
                's.SaleDT as date',
                's.TotalGP as gpamt',
                's.TotalCost as costamt',
                's.TotalNet as netamt',
                DB::raw("COALESCE(e.TotalExp, 0) as expamt"),
                DB::raw("(s.TotalGP - COALESCE(e.TotalExp, 0)) as netamount")
            ])
            ->orderBy('s.SaleDT', $orderBy)
            ->get();
    }

    private function shops()
    {
        return DB::table("salesum")
            ->where("ShopPrefix", auth()->user()->ShopPrefix)
            ->distinct()
            ->orderBy("ShopID")
            ->pluck("ShopID");
    }
}