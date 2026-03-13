<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesSummaryController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $shopId = $request->get("shop_id");
        $startDate = $request->get(
            "start_date",
            now()->subDays(10)->toDateString(),
        );
        $endDate = $request->get("end_date", now()->toDateString());

        $query = DB::table("salesum")
            ->selectRaw(
                'SaleDT,
                SUM(TInv) as TInv,
                SUM(TQty) as TQty,
                SUM(TNetAmt) as SaleAmt
            ',
            )
            ->where("ShopPrefix", $user->ShopPrefix)
            ->whereBetween("SaleDT", [$startDate, $endDate]);

        if ($shopId) {
            $query->where("ShopID", $shopId);
        }

        $data = $query->groupBy("SaleDT")->orderBy("SaleDT", "desc")->get();

        $summary = DB::table("salesum")
            ->selectRaw(
                '
                SUM(TInv) as totalInvoice,
                SUM(TQty) as totalQuantity,
                SUM(TNetAmt) as totalAmount
            ',
            )
            ->where("ShopPrefix", $user->ShopPrefix)
            ->whereBetween("SaleDT", [$startDate, $endDate]);

        if ($shopId) {
            $summary->where("ShopID", $shopId);
        }

        $summary = $summary->first();

        $shops = DB::table("salesum")
            ->select("ShopID")
            ->where("ShopPrefix", $user->ShopPrefix)
            ->distinct()
            ->orderBy("ShopID")
            ->pluck("ShopID");

        return view("reports.sales-summary", [
            "pageTitle" => "Sales Summary",
            "data" => $data,
            "shops" => $shops,
            "summary" => $summary,
            "shopId" => $shopId,
            "startDate" => $startDate,
            "endDate" => $endDate,
        ]);
    }
}
