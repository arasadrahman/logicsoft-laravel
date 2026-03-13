<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view("auth.login");
    }

    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            "username" => ["required"],
            "password" => ["required"],
        ]);

        $field = filter_var($validated["username"], FILTER_VALIDATE_EMAIL)
            ? "email"
            : "username";

        $credentials = [
            $field => $validated["username"],
            "password" => $validated["password"],
            "status" => "A",
        ];

        if (!Auth::attempt($credentials)) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Invalid username or password",
                    "data" => null,
                ],
                401,
            );
        }

        $userId = Auth::id();
        $user = User::find($userId);
        $user->update([
            "LastLogin" => now(),
        ]);

        return response()->json([
            "success" => true,
            "message" => "Login successful",
            "data" => $user,
        ]);
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect("/login");
    }
}
