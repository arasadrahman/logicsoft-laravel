<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\PdfService;
use App\Services\ExcelService;

class InvoiceWiseSalesDetailsController extends Controller
{
    public function index(Request $request)
    {
        $pageTitle = "Invoice Wise Sales Details";

        [$shopId, $startDate, $endDate] = $this->filters($request);

        $shops = $this->shops();
        $rawData = $this->reportData($shopId, $startDate, $endDate);

        $summary = (object)[
            "totalInvoice" => $rawData->unique("Invoice")->count(),
            "totalQuantity" => $rawData->sum("Qty"),
            "totalAmount" => $rawData->sum("SaleAmount"),
        ];
        
        $data = $rawData->groupBy(
            fn($row) => $row->Invoice . "@" . $row->ShopID,
        );

        return view(
            "reports.sales-details-invoice",
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

    $rows = $this->reportData($shopId, $startDate, $endDate, 'ASC');

    $grouped = [];

    foreach ($rows as $row) {

        $key = $row->Invoice . '@' . $row->ShopID;

        if (!isset($grouped[$key])) {
            $grouped[$key] = [
                'invoice' => $row->Invoice,
                'shopId' => $row->ShopID,
                'time' => $row->SaleDT . ' ' . $row->SaleTM,
                'counter' => $row->CounterID,
                'user' => $row->UserID,
                'items' => [],
                'total_qty' => 0,
                'total_grs' => 0,
                'total_disc' => 0,
                'total_net' => 0,
                'unique_items' => []
            ];
        }

        $grouped[$key]['items'][] = $row;
        $grouped[$key]['total_qty'] += $row->Qty;
        $grouped[$key]['total_grs'] += $row->GrsAmt;
        $grouped[$key]['total_disc'] += $row->DiscAmt;
        $grouped[$key]['total_net'] += $row->SaleAmount;
        $grouped[$key]['unique_items'][$row->Item] = true;
    }

    foreach ($grouped as &$g) {
        $g['unique_items'] = count($g['unique_items']);
    }

    return $pdf
            ->make("reports.pdf.sales-details-invoice", [
                "invoices" => $grouped,
                "shopId" => $shopId,
                "startDate" => $startDate,
                "endDate" => $endDate
            ])
            ->fileName("Invoice-Wise-Sales-Details.pdf")
            ->paper("A4", "landscape")
            ->stream();
}

    public function excel(Request $request, ExcelService $excelService)
    {
        
    [$shopId, $startDate, $endDate] = $this->filters($request);

    $rows = $this->reportData($shopId, $startDate, $endDate, 'ASC');

    $grouped = [];

    foreach ($rows as $row) {

        $key = $row->Invoice . '@' . $row->ShopID;

        if (!isset($grouped[$key])) {
            $grouped[$key] = [
                'invoice' => $row->Invoice,
                'shopId' => $row->ShopID,
                'time' => $row->SaleDT . ' ' . $row->SaleTM,
                'counter' => $row->CounterID,
                'user' => $row->UserID,
                'items' => [],
                'total_qty' => 0,
                'total_grs' => 0,
                'total_disc' => 0,
                'total_net' => 0,
                'unique_items' => []
            ];
        }

        $grouped[$key]['items'][] = $row;
        $grouped[$key]['total_qty'] += $row->Qty;
        $grouped[$key]['total_grs'] += $row->GrsAmt;
        $grouped[$key]['total_disc'] += $row->DiscAmt;
        $grouped[$key]['total_net'] += $row->SaleAmount;
        $grouped[$key]['unique_items'][$row->Item] = true;
    }

    foreach ($grouped as &$g) {
        $g['unique_items'] = count($g['unique_items']);
    }

        $sheetTitle = "Sales Summary {$startDate} to {$endDate}";
        $fileName = "sales_summary_invoice_{$startDate}_{$endDate}.xlsx";

        return $excelService->export(
            view("reports.excel.sales-details-invoice", [
                "invoices" => $grouped,
                "shopId" => $shopId,
                "startDate" => $startDate,
                "endDate" => $endDate
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
            ->select(
                "Invoice",
                "Qty",
                "SaleAmount",
                "ShopID",
                "UserID",
                "SaleDT",
                "SaleTM",
                "CounterID",
                "Barcode",
                "Catagory",
                "Item",
                "MRP",
                "GrsAmt",
                "DiscAmt",
            )
            ->where("ShopPrefix", $shopPrefix)
            ->whereBetween("SaleDT", [$startDate, $endDate])
            ->orderBy("ShopID", "ASC")
            ->orderBy("Invoice", $orderDirection);

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
