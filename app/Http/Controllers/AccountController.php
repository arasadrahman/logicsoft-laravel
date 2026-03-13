<?php

namespace App\Http\Controllers;

use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AccountController extends Controller
{
    public function edit()
    {
        $pageTitle = "Account Settings";
        $user = Auth::user();

        return view("account.index", compact("pageTitle", "user"));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            "Email" => [
                "required",
                "email",
                Rule::unique("users", "Email")->ignore($user->id),
            ],
            "Mobile" => ["required", "string", "max:20"],
            "Logo" => ["nullable", "image", "mimes:jpg,jpeg,png", "max:2048"],
        ]);

        if ($request->hasFile("Logo")) {
            $logoName = time() . "." . $request->Logo->extension();
            $request->Logo->move(public_path("uploads/logos"), $logoName);
            $user->Logo = $logoName;
        }

        $user->Email = $request->Email;
        $user->Mobile = $request->Mobile;
        $user->save();

        return redirect()
            ->back()
            ->with("success", "Profile updated successfully");
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            "current_password" => ["required"],
            "password" => ["required", "confirmed", "min:6"],
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->Password)) {
            return back()->withErrors([
                "current_password" => "Current password is incorrect",
            ]);
        }

        $user->Password = Hash::make($request->password);
        $user->save();

        return redirect()
            ->back()
            ->with("success", "Password changed successfully");
    }

    public function billing(): View
    {
        $user = Auth::user();
        $shopPrefix = $user->ShopPrefix;

        $data = DB::table("clienthistory")
            ->where("ShopPrefix", $shopPrefix)
            ->first();

        $license = DB::table("license")
            ->where("ShopPrefix", $shopPrefix)
            ->first();

        if ($license) {
            $data->ExpDT = $license->ExpDT;
        } else {
            $data->ExpDT = null;
        }

        return view("account.billing", [
            "pageTitle" => "Billing Information",
            "data" => $data,
        ]);
    }
}
