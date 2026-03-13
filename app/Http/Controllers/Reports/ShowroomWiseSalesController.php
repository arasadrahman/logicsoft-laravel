<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShowroomWiseSalesController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $shopId = $request->query("shopId");
        $startDate = $request->get("start_date", now()->toDateString());
        $endDate = $request->get("end_date", now()->toDateString());

        $data = DB::table("salesum")
            ->selectRaw(
                '
                ShopID,
                SUM(TInv) as TInv,
                SUM(TQty) as TQty,
                SUM(TCostAmt) as TCostAmt,
                SUM(TNetAmt) as TNetAmt,
                SUM(TGPAmt) as TGPAmt,
                SUM(TDiscAmt+ODiscAmt) as TDiscAmt
            ',
            )
            ->where("ShopPrefix", $user->ShopPrefix)
            ->whereBetween("SaleDT", [$startDate, $endDate])
            ->groupBy("ShopID")
            ->orderBy("ShopID")
            ->get();
        
        $shops = DB::table("saleitem")
            ->select("ShopID")
            ->where("ShopPrefix", auth()->user()->ShopPrefix)
            ->distinct()
            ->orderBy("ShopID")
            ->get();
            
        $saleItemsQuery = DB::table("saleitem")
            ->selectRaw(
                "
                Catagory,
                SUM(Qty) as TQty,
                SUM(CostAmount) as TCostAmt,
                SUM(SaleAmount) as TNetAmt,
                SUM(GPAmt) as TGPAmt,
                SUM(DiscAmt) as TDiscAmt
            ",
            )
            ->where("ShopPrefix", auth()->user()->ShopPrefix)
            ->whereBetween("SaleDT", [$startDate, $endDate]);

        if ($shopId) {
            $saleItemsData = $saleItemsQuery
                ->where("ShopID", $shopId)
                ->groupBy("ShopID", "Catagory")
                ->orderBy("ShopID")
                ->orderBy("Catagory")
                ->get();
            
        } else {
            $saleItemsData = $saleItemsQuery
                ->groupBy("Catagory")
                ->orderBy("Catagory")
                ->get();
        }
        
        return view("reports.showroom-wise-sales", [
            "pageTitle" => "Showroom Wise Sales",
            "shopId" => $shopId,
            "data" => $data,
            "startDate" => $startDate,
            "endDate" => $endDate,
            "shops" => $shops,
            "saleItemsData" => $saleItemsData
        ]);
    }
}
