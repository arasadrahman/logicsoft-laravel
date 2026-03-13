<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReportsApiService
{
    public function indexPayload(User $user): array
    {
        $reports = collect($this->definitions())
            ->map(function (array $report) {
                return [
                    "key" => $report["key"],
                    "title" => $report["title"],
                    "description" => $report["description"],
                    "filters" => $report["filters"],
                    "endpoint" => url("/api/reports/{$report["key"]}"),
                ];
            })
            ->values()
            ->all();

        return [
            "auth" => [
                "client_id" => $user->ClientID,
                "shop_name" => $user->ShopName,
                "shop_prefix" => $user->ShopPrefix,
            ],
            "defaults" => [
                "start_date" => now()->toDateString(),
                "end_date" => now()->toDateString(),
            ],
            "reports" => $reports,
        ];
    }

    public function reportPayload(User $user, string $reportKey, Request $request): array
    {
        $definition = $this->findDefinition($reportKey);
        $filters = $this->filtersFromRequest($request);

        return match ($reportKey) {
            "showroom-wise-sales" => $this->showroomWiseSales($user, $definition, $filters),
            "sales-summary" => $this->salesSummary($user, $definition, $filters),
            "sales-details" => $this->salesDetails($user, $definition, $filters),
            "sales-summary-invoice" => $this->salesSummaryInvoice($user, $definition, $filters),
            "sales-details-invoice" => $this->salesDetailsInvoice($user, $definition, $filters),
            "collection" => $this->collection($user, $definition, $filters),
            "gross-profit-loss" => $this->grossProfitLoss($user, $definition, $filters),
            "item-wise-gross-profit-loss" => $this->itemWiseGrossProfitLoss($user, $definition, $filters),
            "invoice-wise-gross-profit-loss" => $this->invoiceWiseGrossProfitLoss($user, $definition, $filters),
            "daily-expense" => $this->expenseMatrix($user, $definition, $filters, "Daily Expence"),
            "bank-deposit" => $this->expenseMatrix($user, $definition, $filters, "Bank Deposit"),
            "bank-withdraw" => $this->expenseMatrix($user, $definition, $filters, "Bank Withdraw"),
            "supplier-payment" => $this->expenseMatrix($user, $definition, $filters, "Supplier Payment"),
            "none-sale-income" => $this->expenseMatrix($user, $definition, $filters, "Non Sale Income"),
            "present-stock" => $this->presentStock($user, $definition, $filters),
            "net-profit-loss" => $this->netProfitLoss($user, $definition, $filters),
            default => throw ValidationException::withMessages([
                "report" => "Unsupported report [{$reportKey}]",
            ]),
        };
    }

    private function showroomWiseSales(User $user, array $definition, array $filters): array
    {
        $rows = DB::table("salesum")
            ->selectRaw("
                ShopID,
                SUM(TInv) as TInv,
                SUM(TQty) as TQty,
                SUM(TCostAmt) as TCostAmt,
                SUM(TNetAmt) as TNetAmt,
                SUM(TGPAmt) as TGPAmt,
                SUM(TDiscAmt + ODiscAmt) as TDiscAmt
            ")
            ->where("ShopPrefix", $user->ShopPrefix)
            ->whereBetween("SaleDT", [$filters["start_date"], $filters["end_date"]])
            ->groupBy("ShopID")
            ->orderBy("ShopID")
            ->get();

        $saleItemsQuery = DB::table("saleitem")
            ->selectRaw("
                Catagory,
                SUM(Qty) as TQty,
                SUM(CostAmount) as TCostAmt,
                SUM(SaleAmount) as TNetAmt,
                SUM(GPAmt) as TGPAmt,
                SUM(DiscAmt) as TDiscAmt
            ")
            ->where("ShopPrefix", $user->ShopPrefix)
            ->whereBetween("SaleDT", [$filters["start_date"], $filters["end_date"]]);

        if ($filters["shop_id"]) {
            $saleItemsQuery->where("ShopID", $filters["shop_id"]);
        }

        $saleItems = $saleItemsQuery
            ->groupBy("Catagory")
            ->orderBy("Catagory")
            ->get();

        return $this->payload($definition, $filters, [
            "shops" => $this->shopOptionsFromTable("saleitem", $user),
            "summary" => [
                "total_invoice" => $rows->sum("TInv"),
                "total_quantity" => $rows->sum("TQty"),
                "total_cost_amount" => $rows->sum("TCostAmt"),
                "total_sale_amount" => $rows->sum("TNetAmt"),
                "total_gross_profit" => $rows->sum("TGPAmt"),
                "total_discount_amount" => $rows->sum("TDiscAmt"),
            ],
            "rows" => $rows->values()->all(),
            "category_breakdown" => $saleItems->values()->all(),
        ]);
    }

    private function salesSummary(User $user, array $definition, array $filters): array
    {
        $query = DB::table("salesum")
            ->selectRaw("
                SaleDT,
                SUM(TInv) as TInv,
                SUM(TQty) as TQty,
                SUM(TNetAmt) as SaleAmt
            ")
            ->where("ShopPrefix", $user->ShopPrefix)
            ->whereBetween("SaleDT", [$filters["start_date"], $filters["end_date"]]);

        if ($filters["shop_id"]) {
            $query->where("ShopID", $filters["shop_id"]);
        }

        $rows = $query
            ->groupBy("SaleDT")
            ->orderBy("SaleDT", "desc")
            ->get();

        return $this->payload($definition, $filters, [
            "shops" => $this->shopOptionsFromTable("salesum", $user),
            "summary" => [
                "total_invoice" => $rows->sum("TInv"),
                "total_quantity" => $rows->sum("TQty"),
                "total_amount" => $rows->sum("SaleAmt"),
            ],
            "rows" => $rows->values()->all(),
        ]);
    }

    private function salesDetails(User $user, array $definition, array $filters): array
    {
        $rows = $this->salesDetailsRows($user, $filters);
        $grouped = $rows->groupBy("Catagory");

        return $this->payload($definition, $filters, [
            "shops" => $this->shopOptionsFromTable("saleitem", $user),
            "summary" => [
                "total_items" => $rows->count(),
                "total_quantity" => $rows->sum("Qty"),
                "total_amount" => $rows->sum("SaleAmount"),
                "total_discount_amount" => $rows->sum("DiscAmt"),
                "total_gross_amount" => $rows->sum("GrsAmt"),
            ],
            "rows" => $rows->values()->all(),
            "grouped_rows" => $grouped->map(fn (Collection $items) => $items->values()->all())->all(),
        ]);
    }

    private function salesSummaryInvoice(User $user, array $definition, array $filters): array
    {
        $rows = $this->salesSummaryInvoiceRows($user, $filters);

        return $this->payload($definition, $filters, [
            "shops" => $this->shopOptionsFromTable("saleinvoice", $user),
            "summary" => [
                "total_invoice" => $rows->unique("Invoice")->count(),
                "total_quantity" => $rows->sum("Qty"),
                "total_amount" => $rows->sum("SaleAmount"),
            ],
            "rows" => $rows->values()->all(),
            "grouped_by_shop" => $rows
                ->sortBy("ShopID")
                ->groupBy("ShopID")
                ->map(fn (Collection $items) => $items->values()->all())
                ->all(),
        ]);
    }

    private function salesDetailsInvoice(User $user, array $definition, array $filters): array
    {
        $rows = $this->salesDetailsInvoiceRows($user, $filters);

        $invoices = [];
        foreach ($rows as $row) {
            $key = $row->Invoice . "@" . $row->ShopID;

            if (! isset($invoices[$key])) {
                $invoices[$key] = [
                    "invoice" => $row->Invoice,
                    "shop_id" => $row->ShopID,
                    "sale_date" => $row->SaleDT,
                    "sale_time" => $row->SaleTM,
                    "counter_id" => $row->CounterID,
                    "user_id" => $row->UserID,
                    "items" => [],
                    "total_quantity" => 0,
                    "total_gross_amount" => 0,
                    "total_discount_amount" => 0,
                    "total_sale_amount" => 0,
                    "unique_items" => [],
                ];
            }

            $invoices[$key]["items"][] = $row;
            $invoices[$key]["total_quantity"] += (float) $row->Qty;
            $invoices[$key]["total_gross_amount"] += (float) $row->GrsAmt;
            $invoices[$key]["total_discount_amount"] += (float) $row->DiscAmt;
            $invoices[$key]["total_sale_amount"] += (float) $row->SaleAmount;
            $invoices[$key]["unique_items"][$row->Item] = true;
        }

        foreach ($invoices as &$invoice) {
            $invoice["unique_items"] = count($invoice["unique_items"]);
        }
        unset($invoice);

        return $this->payload($definition, $filters, [
            "shops" => $this->shopOptionsFromTable("saleinvoice", $user),
            "summary" => [
                "total_invoice" => count($invoices),
                "total_quantity" => $rows->sum("Qty"),
                "total_amount" => $rows->sum("SaleAmount"),
                "total_discount_amount" => $rows->sum("DiscAmt"),
                "total_gross_amount" => $rows->sum("GrsAmt"),
            ],
            "rows" => $rows->values()->all(),
            "invoices" => array_values($invoices),
        ]);
    }

    private function collection(User $user, array $definition, array $filters): array
    {
        $query = DB::table("saletype")
            ->selectRaw("
                SaleDT,
                PayType,
                SUM(Amount) as Total
            ")
            ->where("ShopPrefix", $user->ShopPrefix)
            ->whereBetween("SaleDT", [$filters["start_date"], $filters["end_date"]])
            ->groupBy("SaleDT", "PayType")
            ->orderBy("SaleDT", "desc");

        if ($filters["shop_id"]) {
            $query->where("ShopID", $filters["shop_id"]);
        }

        $rows = $query->get();
        $payTypes = $rows->pluck("PayType")->unique()->values();
        $table = [];

        foreach ($rows as $row) {
            $table[$row->SaleDT][$row->PayType] = $row->Total;
        }

        return $this->payload($definition, $filters, [
            "shops" => $this->shopOptionsFromTable("saletype", $user),
            "pay_types" => $payTypes->all(),
            "summary" => [
                "grand_total" => $rows->sum("Total"),
            ],
            "rows" => $rows->values()->all(),
            "table" => $table,
        ]);
    }

    private function grossProfitLoss(User $user, array $definition, array $filters): array
    {
        $query = DB::table("salesum")
            ->selectRaw("
                SaleDT,
                SUM(TInv) as Inv,
                SUM(TQty) as Qty,
                SUM(TCostAmt) as CostAmt,
                SUM(TNetAmt) as SaleAmount,
                SUM(TGPAmt) as GPAmt
            ")
            ->where("ShopPrefix", $user->ShopPrefix)
            ->whereBetween("SaleDT", [$filters["start_date"], $filters["end_date"]])
            ->groupBy("SaleDT")
            ->orderBy("SaleDT", "desc");

        if ($filters["shop_id"]) {
            $query->where("ShopID", $filters["shop_id"]);
        }

        $rows = $query->get();

        return $this->payload($definition, $filters, [
            "shops" => $this->shopOptionsFromTable("saleinvoice", $user),
            "summary" => [
                "total_invoice" => $rows->sum("Inv"),
                "total_quantity" => $rows->sum("Qty"),
                "total_cost_amount" => $rows->sum("CostAmt"),
                "total_sale_amount" => $rows->sum("SaleAmount"),
                "total_gross_profit" => $rows->sum("GPAmt"),
            ],
            "rows" => $rows->values()->all(),
        ]);
    }

    private function itemWiseGrossProfitLoss(User $user, array $definition, array $filters): array
    {
        $query = DB::table("saleitem")
            ->selectRaw("
                Catagory,
                Barcode,
                Item,
                MRP,
                SUM(Qty) as Qty,
                SUM(CostAmount) as CostAmt,
                SUM(SaleAmount) as SaleAmount,
                SUM(GPAmt) as GPAmt
            ")
            ->where("ShopPrefix", $user->ShopPrefix)
            ->whereBetween("SaleDT", [$filters["start_date"], $filters["end_date"]])
            ->groupBy("Catagory", "Barcode", "Item", "MRP")
            ->orderBy("Item", "desc")
            ->orderBy("Catagory", "desc");

        if ($filters["shop_id"]) {
            $query->where("ShopID", $filters["shop_id"]);
        }

        $rows = $query->get();

        return $this->payload($definition, $filters, [
            "shops" => $this->shopOptionsFromTable("saleinvoice", $user),
            "summary" => [
                "total_rows" => $rows->count(),
                "total_quantity" => $rows->sum("Qty"),
                "total_cost_amount" => $rows->sum("CostAmt"),
                "total_sale_amount" => $rows->sum("SaleAmount"),
                "total_gross_profit" => $rows->sum("GPAmt"),
            ],
            "rows" => $rows->values()->all(),
            "grouped_rows" => $rows
                ->groupBy("Catagory")
                ->map(fn (Collection $items) => $items->values()->all())
                ->all(),
        ]);
    }

    private function invoiceWiseGrossProfitLoss(User $user, array $definition, array $filters): array
    {
        $query = DB::table("saleinvoice")
            ->selectRaw("
                Invoice,
                SUM(Qty) as Qty,
                SUM(CostAmount) as CostAmount,
                SUM(SaleAmount) as SaleAmount,
                SUM(GPAmt) as GPAmt,
                ShopID,
                UserID,
                SaleDT,
                SaleTM,
                CounterID
            ")
            ->where("ShopPrefix", $user->ShopPrefix)
            ->whereBetween("SaleDT", [$filters["start_date"], $filters["end_date"]])
            ->groupBy("Invoice", "ShopID", "UserID", "SaleDT", "SaleTM", "CounterID")
            ->orderBy("ShopID")
            ->orderBy("Invoice", "desc");

        if ($filters["shop_id"]) {
            $query->where("ShopID", $filters["shop_id"]);
        }

        $rows = $query->get();

        return $this->payload($definition, $filters, [
            "shops" => $this->shopOptionsFromTable("saleinvoice", $user),
            "summary" => [
                "total_rows" => $rows->count(),
                "total_quantity" => $rows->sum("Qty"),
                "total_cost_amount" => $rows->sum("CostAmount"),
                "total_sale_amount" => $rows->sum("SaleAmount"),
                "total_gross_profit" => $rows->sum("GPAmt"),
            ],
            "rows" => $rows->values()->all(),
        ]);
    }

    private function expenseMatrix(User $user, array $definition, array $filters, string $type): array
    {
        $query = DB::table("saleexpence")
            ->selectRaw("
                SaleDT,
                ExpHead,
                SUM(Amount) as Total
            ")
            ->where("Type", $type)
            ->where("ShopPrefix", $user->ShopPrefix)
            ->whereBetween("SaleDT", [$filters["start_date"], $filters["end_date"]])
            ->groupBy("SaleDT", "ExpHead")
            ->orderBy("SaleDT", "desc")
            ->orderBy("ExpHead", "desc");

        if ($filters["shop_id"]) {
            $query->where("ShopID", $filters["shop_id"]);
        }

        $rows = $query->get();
        $heads = $rows->pluck("ExpHead")->unique()->values();
        $table = [];
        $columnTotals = array_fill_keys($heads->all(), 0);
        $grandTotal = 0;

        foreach ($rows as $row) {
            $date = $row->SaleDT;
            $head = $row->ExpHead;
            $amount = (float) $row->Total;

            if (! isset($table[$date])) {
                $table[$date] = array_fill_keys($heads->all(), 0);
                $table[$date]["_total"] = 0;
            }

            $table[$date][$head] = $amount;
            $table[$date]["_total"] += $amount;
            $columnTotals[$head] += $amount;
            $grandTotal += $amount;
        }

        $chunkSize = match (true) {
            $heads->count() > 40 => 6,
            $heads->count() > 30 => 8,
            $heads->count() > 20 => 10,
            default => 12,
        };

        return $this->payload($definition, $filters, [
            "shops" => $this->shopOptionsFromExpenseType($user, $type),
            "heads" => $heads->all(),
            "summary" => [
                "grand_total" => $grandTotal,
            ],
            "rows" => $rows->values()->all(),
            "table" => $table,
            "column_totals" => $columnTotals,
            "chunk_size" => $chunkSize,
        ]);
    }

    private function presentStock(User $user, array $definition, array $filters): array
    {
        $query = DB::table("stock")
            ->selectRaw("
                MAX(StockDT) AS StockDT,
                MAX(StockTM) AS StockTM,
                MAX(ShopID) AS ShopID,
                GroupName,
                PrdName,
                SUM(TQty) AS TQty,
                SUM(TCP) AS TCP,
                SUM(TSP) AS TSP
            ")
            ->where("ShopPrefix", $user->ShopPrefix)
            ->groupBy("GroupName", "PrdName")
            ->orderBy("GroupName")
            ->orderBy("PrdName");

        if ($filters["shop_id"]) {
            $query->where("ShopID", $filters["shop_id"]);
        }

        if ($filters["category"]) {
            $query->where("GroupName", $filters["category"]);
        }

        $rows = $query->get();
        $firstRow = $rows->first();

        return $this->payload($definition, $filters, [
            "shops" => $this->shopOptionsFromTable("stock", $user),
            "categories" => $this->stockCategories($user),
            "summary" => [
                "total_quantity" => $rows->sum("TQty"),
                "total_cost_price" => $rows->sum("TCP"),
                "total_sale_price" => $rows->sum("TSP"),
                "stock_snapshot_at" => $firstRow ? "{$firstRow->StockDT} {$firstRow->StockTM}" : null,
            ],
            "rows" => $rows->values()->all(),
        ]);
    }

    private function netProfitLoss(User $user, array $definition, array $filters): array
    {
        $salesSub = DB::table("salesum")
            ->select("SaleDT")
            ->selectRaw("SUM(TGPAmt) AS TotalGP")
            ->selectRaw("SUM(TCostAmt) AS TotalCost")
            ->selectRaw("SUM(TNetAmt) AS TotalNet")
            ->where("ShopPrefix", $user->ShopPrefix)
            ->whereBetween("SaleDT", [$filters["start_date"], $filters["end_date"]])
            ->when($filters["shop_id"], fn ($query) => $query->where("ShopID", $filters["shop_id"]))
            ->groupBy("SaleDT");

        $expenseSub = DB::table("saleexpence")
            ->select("SaleDT")
            ->selectRaw("SUM(Amount) AS TotalExp")
            ->where("ShopPrefix", $user->ShopPrefix)
            ->where("Type", "Daily Expence")
            ->whereBetween("SaleDT", [$filters["start_date"], $filters["end_date"]])
            ->when($filters["shop_id"], fn ($query) => $query->where("ShopID", $filters["shop_id"]))
            ->groupBy("SaleDT");

        $rows = DB::table(DB::raw("({$salesSub->toSql()}) as s"))
            ->mergeBindings($salesSub)
            ->leftJoin(DB::raw("({$expenseSub->toSql()}) as e"), "s.SaleDT", "=", "e.SaleDT")
            ->mergeBindings($expenseSub)
            ->select([
                "s.SaleDT as date",
                "s.TotalGP as gpamt",
                "s.TotalCost as costamt",
                "s.TotalNet as netamt",
                DB::raw("COALESCE(e.TotalExp, 0) as expamt"),
                DB::raw("(s.TotalGP - COALESCE(e.TotalExp, 0)) as netamount"),
            ])
            ->orderBy("s.SaleDT", "desc")
            ->get();

        return $this->payload($definition, $filters, [
            "shops" => $this->shopOptionsFromTable("salesum", $user),
            "summary" => [
                "gross_profit_amount" => $rows->sum("gpamt"),
                "expense_amount" => $rows->sum("expamt"),
                "net_profit_amount" => $rows->sum("netamount"),
            ],
            "rows" => $rows->values()->all(),
        ]);
    }

    private function salesDetailsRows(User $user, array $filters): Collection
    {
        $query = DB::table("saleitem")
            ->selectRaw("
                Catagory,
                Item,
                Barcode,
                MRP,
                SUM(Qty) as Qty,
                SUM(SaleAmount) as SaleAmount,
                SUM(DiscAmt) as DiscAmt,
                SUM(GrsAmt) as GrsAmt
            ")
            ->where("ShopPrefix", $user->ShopPrefix)
            ->whereBetween("SaleDT", [$filters["start_date"], $filters["end_date"]])
            ->groupBy("Catagory", "Item", "Barcode", "MRP")
            ->orderBy("Catagory")
            ->orderBy("GrsAmt", "desc");

        if ($filters["shop_id"]) {
            $query->where("ShopID", $filters["shop_id"]);
        }

        return $query->get();
    }

    private function salesSummaryInvoiceRows(User $user, array $filters): Collection
    {
        $query = DB::table("saleinvoice")
            ->selectRaw("
                Invoice,
                SUM(Qty) as Qty,
                SUM(SaleAmount) as SaleAmount,
                PayType,
                ShopID,
                UserID,
                SaleDT,
                SaleTM,
                CounterID
            ")
            ->where("ShopPrefix", $user->ShopPrefix)
            ->whereBetween("SaleDT", [$filters["start_date"], $filters["end_date"]])
            ->groupBy("Invoice", "PayType", "ShopID", "UserID", "SaleDT", "SaleTM", "CounterID")
            ->orderBy("ShopID")
            ->orderBy("Invoice", "desc");

        if ($filters["shop_id"]) {
            $query->where("ShopID", $filters["shop_id"]);
        }

        return $query->get();
    }

    private function salesDetailsInvoiceRows(User $user, array $filters): Collection
    {
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
            ->where("ShopPrefix", $user->ShopPrefix)
            ->whereBetween("SaleDT", [$filters["start_date"], $filters["end_date"]])
            ->orderBy("ShopID", "asc")
            ->orderBy("Invoice", "desc");

        if ($filters["shop_id"]) {
            $query->where("ShopID", $filters["shop_id"]);
        }

        return $query->get();
    }

    private function shopOptionsFromTable(string $table, User $user): array
    {
        return DB::table($table)
            ->select("ShopID")
            ->where("ShopPrefix", $user->ShopPrefix)
            ->distinct()
            ->orderBy("ShopID")
            ->pluck("ShopID")
            ->values()
            ->all();
    }

    private function shopOptionsFromExpenseType(User $user, string $type): array
    {
        return DB::table("saleexpence")
            ->select("ShopID")
            ->where("ShopPrefix", $user->ShopPrefix)
            ->where("Type", $type)
            ->distinct()
            ->orderBy("ShopID")
            ->pluck("ShopID")
            ->values()
            ->all();
    }

    private function stockCategories(User $user): array
    {
        return DB::table("stock")
            ->select("GroupName")
            ->where("ShopPrefix", $user->ShopPrefix)
            ->distinct()
            ->orderBy("GroupName")
            ->pluck("GroupName")
            ->values()
            ->all();
    }

    private function filtersFromRequest(Request $request): array
    {
        $startDate = $request->input("start_date", $request->input("startDate", now()->toDateString()));
        $endDate = $request->input("end_date", $request->input("endDate", now()->toDateString()));

        validator(
            [
                "start_date" => $startDate,
                "end_date" => $endDate,
            ],
            [
                "start_date" => ["required", "date"],
                "end_date" => ["required", "date", "after_or_equal:start_date"],
            ],
        )->validate();

        return [
            "shop_id" => $request->input("shop_id", $request->input("shopId")),
            "start_date" => $startDate,
            "end_date" => $endDate,
            "category" => $request->input("category", $request->input("cat")),
        ];
    }

    private function payload(array $definition, array $filters, array $data): array
    {
        return array_merge([
            "key" => $definition["key"],
            "title" => $definition["title"],
            "description" => $definition["description"],
            "filters" => $filters,
        ], $data);
    }

    private function findDefinition(string $reportKey): array
    {
        foreach ($this->definitions() as $definition) {
            if ($definition["key"] === $reportKey) {
                return $definition;
            }
        }

        throw ValidationException::withMessages([
            "report" => "Unknown report [{$reportKey}]",
        ]);
    }

    private function definitions(): array
    {
        return [
            [
                "key" => "showroom-wise-sales",
                "title" => "Showroom Wise Sales",
                "description" => "Shop-level sales totals with category breakdown.",
                "filters" => ["shop_id", "start_date", "end_date"],
            ],
            [
                "key" => "sales-summary",
                "title" => "Sales Summary",
                "description" => "Date-wise sales summary totals.",
                "filters" => ["shop_id", "start_date", "end_date"],
            ],
            [
                "key" => "sales-details",
                "title" => "Sales Details",
                "description" => "Category and item-level sales detail rows.",
                "filters" => ["shop_id", "start_date", "end_date"],
            ],
            [
                "key" => "sales-summary-invoice",
                "title" => "Invoice Wise Sales Summary",
                "description" => "Invoice-level sales totals grouped by shop.",
                "filters" => ["shop_id", "start_date", "end_date"],
            ],
            [
                "key" => "sales-details-invoice",
                "title" => "Invoice Wise Sales Details",
                "description" => "Line-item invoice detail for the selected date range.",
                "filters" => ["shop_id", "start_date", "end_date"],
            ],
            [
                "key" => "collection",
                "title" => "Daily Collection",
                "description" => "Daily collection matrix by payment type.",
                "filters" => ["shop_id", "start_date", "end_date"],
            ],
            [
                "key" => "gross-profit-loss",
                "title" => "Date Wise Gross Profit",
                "description" => "Daily gross profit totals.",
                "filters" => ["shop_id", "start_date", "end_date"],
            ],
            [
                "key" => "item-wise-gross-profit-loss",
                "title" => "Item Wise Gross Profit / Loss",
                "description" => "Gross profit per item with category grouping.",
                "filters" => ["shop_id", "start_date", "end_date"],
            ],
            [
                "key" => "invoice-wise-gross-profit-loss",
                "title" => "Invoice Wise Gross Profit / Loss",
                "description" => "Gross profit totals per invoice.",
                "filters" => ["shop_id", "start_date", "end_date"],
            ],
            [
                "key" => "daily-expense",
                "title" => "Daily Expense",
                "description" => "Expense matrix for Daily Expence rows.",
                "filters" => ["shop_id", "start_date", "end_date"],
            ],
            [
                "key" => "bank-deposit",
                "title" => "Bank Deposit",
                "description" => "Expense matrix for Bank Deposit rows.",
                "filters" => ["shop_id", "start_date", "end_date"],
            ],
            [
                "key" => "bank-withdraw",
                "title" => "Bank Withdraw",
                "description" => "Expense matrix for Bank Withdraw rows.",
                "filters" => ["shop_id", "start_date", "end_date"],
            ],
            [
                "key" => "supplier-payment",
                "title" => "Supplier Payment",
                "description" => "Expense matrix for Supplier Payment rows.",
                "filters" => ["shop_id", "start_date", "end_date"],
            ],
            [
                "key" => "none-sale-income",
                "title" => "None Sale Income",
                "description" => "Expense matrix for Non Sale Income rows.",
                "filters" => ["shop_id", "start_date", "end_date"],
            ],
            [
                "key" => "present-stock",
                "title" => "Present Stock",
                "description" => "Current stock by group and product.",
                "filters" => ["shop_id", "category"],
            ],
            [
                "key" => "net-profit-loss",
                "title" => "Date Wise Net Profit & Loss",
                "description" => "Daily net profit after daily expense.",
                "filters" => ["shop_id", "start_date", "end_date"],
            ],
        ];
    }
}
