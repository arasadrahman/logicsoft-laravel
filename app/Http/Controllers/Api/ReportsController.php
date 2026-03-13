<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ReportsApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportsController extends Controller
{
    public function __construct(private ReportsApiService $reportsApi)
    {
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json([
            "success" => true,
            "message" => "Reports index loaded successfully",
            "data" => $this->reportsApi->indexPayload($request->user()),
        ]);
    }

    public function show(Request $request, string $report): JsonResponse
    {
        return response()->json([
            "success" => true,
            "message" => "Report loaded successfully",
            "data" => $this->reportsApi->reportPayload($request->user(), $report, $request),
        ]);
    }
}
