<?php

namespace App\Http\Controllers\Api;

use App\Models\Asset;
use App\Models\AuditLog;
use App\Models\Section;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends BaseController
{
    public function index(Request $request)
    {
        $recentActivities = AuditLog::with('user:id,name')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $stats = [
            'total_users' => User::count(),
            'total_sections' => Section::count(),
            'total_assets' => Asset::count(),
        ];

        return $this->successResponse([
            'activities' => $recentActivities,
            'stats' => $stats,
        ], 'Dashboard data retrieved.');
    }
}
