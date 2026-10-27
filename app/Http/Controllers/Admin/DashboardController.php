<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\AdminStatsService;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function __invoke(AdminStatsService $stats): JsonResponse
    {
        return response()->json($stats->summary());
    }
}
