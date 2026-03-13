<?php

use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

uses(WithFaker::class);

beforeEach(function () {
    Schema::create("users", function (Blueprint $table) {
        $table->id();
        $table->string("ClientID")->nullable();
        $table->string("ShopName")->nullable();
        $table->string("ShopPrefix")->nullable();
        $table->string("Logo")->nullable();
        $table->string("UserName")->nullable();
        $table->string("Password")->nullable();
        $table->string("Mobile")->nullable();
        $table->string("Email")->nullable();
        $table->string("Status")->nullable();
        $table->dateTime("EntryDT")->nullable();
        $table->dateTime("LastLogin")->nullable();
        $table->rememberToken();
    });

    Schema::create("salesum", function (Blueprint $table) {
        $table->string("ShopPrefix")->nullable();
        $table->string("ShopID")->nullable();
        $table->date("SaleDT")->nullable();
        $table->integer("TInv")->default(0);
        $table->decimal("TQty", 12, 2)->default(0);
        $table->decimal("TCostAmt", 12, 2)->default(0);
        $table->decimal("TNetAmt", 12, 2)->default(0);
        $table->decimal("TGPAmt", 12, 2)->default(0);
        $table->decimal("TDiscAmt", 12, 2)->default(0);
        $table->decimal("ODiscAmt", 12, 2)->default(0);
    });
});

it("lists all available report endpoints for the authenticated user", function () {
    $user = User::unguarded(function () {
        return User::create([
            "ClientID" => "C-100",
            "ShopName" => "Logic Soft",
            "ShopPrefix" => "LS",
            "UserName" => "demo",
            "Password" => bcrypt("secret"),
            "Status" => "A",
        ]);
    });

    $response = $this->actingAs($user)->getJson("/api/reports");

    $response
        ->assertOk()
        ->assertJsonPath("success", true)
        ->assertJsonPath("data.auth.shop_prefix", "LS")
        ->assertJsonCount(16, "data.reports");
});

it("returns sales summary data for expo clients", function () {
    $user = User::unguarded(function () {
        return User::create([
            "ClientID" => "C-100",
            "ShopName" => "Logic Soft",
            "ShopPrefix" => "LS",
            "UserName" => "demo",
            "Password" => bcrypt("secret"),
            "Status" => "A",
        ]);
    });

    DB::table("salesum")->insert([
        [
            "ShopPrefix" => "LS",
            "ShopID" => "SHOP-01",
            "SaleDT" => "2026-03-10",
            "TInv" => 2,
            "TQty" => 4,
            "TCostAmt" => 100,
            "TNetAmt" => 150,
            "TGPAmt" => 50,
            "TDiscAmt" => 5,
            "ODiscAmt" => 0,
        ],
        [
            "ShopPrefix" => "LS",
            "ShopID" => "SHOP-01",
            "SaleDT" => "2026-03-10",
            "TInv" => 1,
            "TQty" => 3,
            "TCostAmt" => 60,
            "TNetAmt" => 90,
            "TGPAmt" => 30,
            "TDiscAmt" => 2,
            "ODiscAmt" => 1,
        ],
    ]);

    $response = $this
        ->actingAs($user)
        ->getJson("/api/reports/sales-summary?shop_id=SHOP-01&start_date=2026-03-01&end_date=2026-03-14");

    $response
        ->assertOk()
        ->assertJsonPath("data.key", "sales-summary")
        ->assertJsonPath("data.summary.total_invoice", 3)
        ->assertJsonPath("data.summary.total_quantity", 7)
        ->assertJsonPath("data.summary.total_amount", 240)
        ->assertJsonPath("data.rows.0.SaleDT", "2026-03-10");
});
