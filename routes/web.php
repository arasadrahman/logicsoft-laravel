<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\Api\ReportsController;

use App\Http\Controllers\Reports\ShowroomWiseSalesController;
use App\Http\Controllers\Reports\SalesSummaryController;
use App\Http\Controllers\Reports\SalesDetailsController;
use App\Http\Controllers\Reports\SalesSummaryInvoiceController;
use App\Http\Controllers\Reports\InvoiceWiseSalesDetailsController;
use App\Http\Controllers\Reports\CollectionController;
use App\Http\Controllers\Reports\GrossProfitLossController;
use App\Http\Controllers\Reports\ItemWiseGrossProfitLossController;
use App\Http\Controllers\Reports\InvoiceWiseGrossProfitLossController;
use App\Http\Controllers\Reports\DailyExpenseController;
use App\Http\Controllers\Reports\BankDepositController;
use App\Http\Controllers\Reports\BankWithdrawController;
use App\Http\Controllers\Reports\SupplierPaymentController;
use App\Http\Controllers\Reports\NoneSaleIncomeController;
use App\Http\Controllers\Reports\PresentStockController;
use App\Http\Controllers\Reports\NetProfitLossController;

Route::middleware(["auth"])->group(function () {
    Route::prefix("api/reports")->name("api.reports.")->group(function () {
        Route::get("/", [ReportsController::class, "index"])->name("index");
        Route::get("/{report}", [ReportsController::class, "show"])->name("show");
    });

    Route::get("/dashboard", [DashboardController::class, "index"])->name(
        "dashboard"
    );

    Route::get("/showroom-wish-sales", [
        ShowroomWiseSalesController::class,
        "index",
    ])->name("showroom.wise.sales");
    
    // Sales Summary
    Route::prefix('sales-summary')->name('sales.summary')->group(function () {
        Route::get('/', [SalesSummaryController::class, 'index'])->name('');
        Route::get('/pdf', [SalesSummaryController::class, 'pdf'])->name('.pdf');
        Route::get('/excel', [SalesSummaryController::class, 'excel'])->name('.excel');
    });
    
    // Sales Details
    Route::get("/sales-details", [
        SalesDetailsController::class,
        "index",
    ])->name("sales.details");
    
    Route::get("/sales-details/pdf", [
        SalesDetailsController::class,
        "pdf",
    ])->name("sales.details.pdf");

    Route::get("/sales-details/excel", [
        SalesDetailsController::class,
        "excel",
    ])->name("sales.details.excel");
    
    
    // Invoice Wise Sales Summary
    Route::prefix('sales-summary-invoice')->name('sales.summary.invoice')->group(function () {
        Route::get('/', [SalesSummaryInvoiceController::class, 'index'])->name('');
        Route::get('/pdf', [SalesSummaryInvoiceController::class, 'pdf'])->name('.pdf');
        Route::get('/excel', [SalesSummaryInvoiceController::class, 'excel'])->name('.excel');
    });
    
    //Invoice Wise Sales Details
    Route::prefix('sales-details-invoice')->name('sales.details.invoice')->group(function () {
        Route::get('/', [InvoiceWiseSalesDetailsController::class, 'index'])->name('');
        Route::get('/pdf', [InvoiceWiseSalesDetailsController::class, 'pdf'])->name('.pdf');
        Route::get('/excel', [InvoiceWiseSalesDetailsController::class, 'excel'])->name('.excel');
    });
    
    // Sale Collection
    Route::prefix('collection')->name('collection')->group(function () {
        Route::get('/', [CollectionController::class, 'index'])->name('');
        Route::get('/pdf', [CollectionController::class, 'pdf'])->name('.pdf');
        Route::get('/excel', [CollectionController::class, 'excel'])->name('.excel');
    });

    // Date Wise Gross Profit
    Route::get("/gross-profit-loss", [
        GrossProfitLossController::class,
        "index",
    ])->name("gross.profit.loss");

    Route::get("/gross-profit-loss/pdf", [
        GrossProfitLossController::class,
        "pdf",
    ])->name("gross.profit.loss.pdf");

    Route::get("/gross-profit-loss/excel", [
        GrossProfitLossController::class,
        "excel",
    ])->name("gross.profit.loss.excel");

    // Item Wise Gross Profit
    Route::prefix('item-wise-gross-profit-loss')->name('item.wise.gross.profit.loss')->group(function () {
        Route::get('/', [ItemWiseGrossProfitLossController::class, 'index'])->name('');
        Route::get('/pdf', [ItemWiseGrossProfitLossController::class, 'pdf'])->name('.pdf');
        Route::get('/excel', [ItemWiseGrossProfitLossController::class, 'excel'])->name('.excel');
    });

    // Invoice Wise Gross Profit
    Route::prefix('invoice-wise-gross-profit-loss')->name('invoice.wise.gross.profit.loss')->group(function () {
        Route::get('/', [InvoiceWiseGrossProfitLossController::class, 'index'])->name('');
        Route::get('/pdf', [InvoiceWiseGrossProfitLossController::class, 'pdf'])->name('.pdf');
        Route::get('/excel', [InvoiceWiseGrossProfitLossController::class, 'excel'])->name('.excel');
    });
    
    // Daily Expense
    Route::prefix('daily-expense')->name('daily.expense')->group(function () {
        Route::get('/', [DailyExpenseController::class, 'index'])->name('');
        Route::get('/pdf', [DailyExpenseController::class, 'pdf'])->name('.pdf');
        Route::get('/excel', [DailyExpenseController::class, 'excel'])->name('.excel');
    });
    
    // Bank Deposit
    Route::prefix('bank-deposit')->name('bank.deposit')->group(function () {
        Route::get('/', [BankDepositController::class, 'index'])->name('');
        Route::get('/pdf', [BankDepositController::class, 'pdf'])->name('.pdf');
        Route::get('/excel', [BankDepositController::class, 'excel'])->name('.excel');
    });
    
    // Bank Deposit
    Route::prefix('bank-withdraw')->name('bank.withdraw')->group(function () {
        Route::get('/', [BankWithdrawController::class, 'index'])->name('');
        Route::get('/pdf', [BankWithdrawController::class, 'pdf'])->name('.pdf');
        Route::get('/excel', [BankWithdrawController::class, 'excel'])->name('.excel');
    });
    
    // Supplier Payment
    Route::prefix('supplier-payment')->name('supplier.payment')->group(function () {
        Route::get('/', [SupplierPaymentController::class, 'index'])->name('');
        Route::get('/pdf', [SupplierPaymentController::class, 'pdf'])->name('.pdf');
        Route::get('/excel', [SupplierPaymentController::class, 'excel'])->name('.excel');
    });
    
    // Non Sale Collection
    Route::prefix('none-sale-income')->name('none.sale.income')->group(function () {
        Route::get('/', [NoneSaleIncomeController::class, 'index'])->name('');
        Route::get('/pdf', [NoneSaleIncomeController::class, 'pdf'])->name('.pdf');
        Route::get('/excel', [NoneSaleIncomeController::class, 'excel'])->name('.excel');
    });
    
    // Present Stock
    Route::prefix('present-stock')->name('present.stock')->group(function () {
        Route::get('/', [PresentStockController::class, 'index'])->name('');
        Route::get('/pdf', [PresentStockController::class, 'pdf'])->name('.pdf');
        Route::get('/excel', [PresentStockController::class, 'excel'])->name('.excel');
    });
    
    // Net Profit Loss
    Route::prefix('net-profit-loss')->name('net.profit.loss')->group(function () {
        Route::get('/', [NetProfitLossController::class, 'index'])->name('');
        Route::get('/pdf', [NetProfitLossController::class, 'pdf'])->name('.pdf');
        Route::get('/excel', [NetProfitLossController::class, 'excel'])->name('.excel');
    });

    Route::get("/billing", [AccountController::class, "billing"])->name(
        "billing",
    );
    Route::get("/change-password", [
        AccountController::class,
        "changePassword",
    ])->name("change.password");

    Route::post("/logout", [AuthController::class, "logout"])->name("logout");

    Route::get("/profile", [AccountController::class, "edit"])->name(
        "account.edit",
    );

    Route::post("/profile/update", [AccountController::class, "update"])->name(
        "account.update",
    );

    Route::post("/profile/password", [
        AccountController::class,
        "updatePassword",
    ])->name("account.password");
});

Route::get("/login", [AuthController::class, "showLogin"])->name("login");
Route::post("/login", [AuthController::class, "login"]);

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});
