<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(): View
    {
        $pageTitle = "Dashboard";

        $user = auth()->user();
        $shopPrefix = $user->ShopPrefix;
        $today = Carbon::today()->toDateString();

        $data = DB::table("salesum")
            ->select(
                "ShopID",
                DB::raw("SUM(TQty) as TQty"),
                DB::raw("SUM(TInv) as TInv"),
                DB::raw("SUM(TNetAmt) as TNetAmt"),
            )
            ->where("ShopPrefix", $shopPrefix)
            ->whereDate("SaleDT", $today)
            ->groupBy("ShopID")
            ->orderBy("ShopID")
            ->get();

        $summary = DB::table("salesum")
            ->select(
                DB::raw("SUM(TQty) as totalQty"),
                DB::raw("SUM(TInv) as totalInv"),
                DB::raw("SUM(TNetAmt) as totalAmount"),
            )
            ->where("ShopPrefix", $shopPrefix)
            ->whereDate("SaleDT", $today)
            ->first();

        $stockData = DB::table("stock")
            ->select(
                "ShopID",
                DB::raw("SUM(TQty) as TQty"),
                DB::raw("SUM(TCP) as TCP"),
                DB::raw("SUM(TSP) as TSP"),
            )
            ->where("ShopPrefix", $shopPrefix)
            ->groupBy("ShopID")
            ->orderBy("ShopID")
            ->get();

        $stockSummary = DB::table("stock")
            ->select(
                DB::raw("SUM(TQty) as sq"),
                DB::raw("SUM(TCP) as stca"),
                DB::raw("SUM(TSP) as stsa"),
            )
            ->where("ShopPrefix", $shopPrefix)
            ->first();

        $productSales = DB::table("saleitem")
            ->select("Catagory", DB::raw("SUM(Qty) as Qty"))
            ->where("ShopPrefix", $shopPrefix)
            ->whereDate("SaleDT", $today)
            ->groupBy("Catagory")
            ->orderBy("Catagory")
            ->get();

        $last10Days = DB::table("salesum")
            ->select(
                "SaleDT",
                DB::raw("SUM(TQty) as Qty"),
                DB::raw("SUM(TNetAmt) as Amount"),
            )
            ->where("ShopPrefix", $shopPrefix)
            ->whereDate("SaleDT", ">=", Carbon::now()->subDays(9))
            ->groupBy("SaleDT")
            ->orderBy("SaleDT")
            ->get()
            ->map(function ($row) {
                return [
                    "SaleDT" => Carbon::parse($row->SaleDT)->format("M j"),
                    "Qty" => (int) $row->Qty,
                    "Amount" => (float) $row->Amount,
                ];
            });

        return view("dashboard.index", [
            "pageTitle" => $pageTitle,
            "user" => $user,
            "data" => $data,
            "summary" => $summary,
            "stockData" => $stockData,
            "stockSummary" => $stockSummary,
            "productSales" => $productSales,
            "last10Days" => $last10Days,
        ]);
    }
}
