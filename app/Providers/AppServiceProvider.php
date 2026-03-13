<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use App\Providers\PlainUserProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Blade;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $sidebarMenu = [
            [
                "title" => "Dashboard",
                "icon" => "fas fa-tachometer-alt",
                "route" => "dashboard",
            ],
            
            [
                "title" => "Showroom Wise Sales",
                "icon" => "fas fa-building",
                "route" => "showroom.wise.sales",
            ],
            
            [
                "title" => "Sales Reports",
                "icon" => "fas fa-chart-line",
                "group" => true,
                "routes_pattern" => [
                    "reports.sales*",
                    "sales.*",
                    "collection",
                ],
                "children" => [
                    ["title" => "Sales Summary", "route" => "sales.summary"],
                    ["title" => "Sales Details", "route" => "sales.details"],
                    [
                        "title" => "Invoice Wise Summary",
                        "route" => "sales.summary.invoice",
                    ],
                    [
                        "title" => "Invoice Wise Details",
                        "route" => "sales.details.invoice",
                    ],
                    ["title" => "Sale Collection", "route" => "collection"],
                ],
            ],

            [
                "title" => "Profit / Loss",
                "icon" => "fas fa-balance-scale",
                "group" => true,
                "routes_pattern" => [
                    "gross.profit.loss",
                    "item.wise.gross.profit.loss",
                    "invoice.wise.gross.profit.loss",
                ],
                "children" => [
                    ["title" => "Date Wise", "route" => "gross.profit.loss"],
                    [
                        "title" => "Item Wise",
                        "route" => "item.wise.gross.profit.loss",
                    ],
                    [
                        "title" => "Invoice Wise",
                        "route" => "invoice.wise.gross.profit.loss",
                    ],
                ],
            ],
            
            [
                "title" => "Income & Expense",
                "icon" => "fas fa-boxes",
                "group" => true,
                "routes_pattern" => [
                    "daily.expense",
                    "bank.deposit",
                    "bank.withdraw",
                    "supplier.payment",
                    "none.sale.income",
                ],
                "children" => [
                    [
                        "title" => "Daily Expense",
                        "route" => "daily.expense"
                    ],
                    [
                        "title" => "Bank Deposit",
                        "route" => "bank.deposit"
                    ],
                    [
                        "title" => "Bank Withdraw",
                        "route" => "bank.withdraw"
                    ],
                    [
                        "title" => "Supplier Payment",
                        "route" => "supplier.payment",
                    ],
                    [
                        "title" => "None Sale Income",
                        "route" => "none.sale.income",
                    ],
                ],
            ],
            
            [
                "title" => "Present Stock",
                "icon" => "fas fa-warehouse",
                "route" => "present.stock"
            ],
            
            [
                "title" => "Net Profit & Loss",
                "icon" => "fas fa-comment-dollar",
                "route" => "net.profit.loss"
            ],

            [
                "header" => "Account",
            ],
            [
                "title" => "Account",
                "icon" => "fas fa-user",
                "route" => "account.edit",
            ],
            [
                "title" => "Billing",
                "icon" => "fas fa-history",
                "route" => "billing",
            ],
        ];

        View::share("sidebarMenu", $sidebarMenu);
        
        Blade::directive('number', function ($expression) {
            return "<?php echo Number::format($expression, precision: 2, locale: 'en_IN'); ?>";
        });

    }
}
