<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $query = Sale::query();

        // If user is a seller, only show their branch's sales
        if ($user->role === 'seller') {
            $query->where('branch_id', $user->branch_id);
        } else {
            // For admins, only show sales from their business's branches
            $query->whereHas('branch.business', function ($q) use ($user) {
                $q->where('owner_id', $user->id)
                    ->orWhereHas('admins', function ($query) use ($user) {
                        $query->where('admin_id', $user->id);
                    });
            });
        }

        // Get today's sales
        $todaySales = $query->whereDate('created_at', today())
            ->with(['seller', 'branch.business'])
            ->get();

        // Get yesterday's sales for comparison
        $yesterdaySales = $query->whereDate('created_at', today()->subDay())
            ->get();

        // Calculate stats
        $totalSales = $todaySales->sum('total_amount');
        $salesCount = $todaySales->count();
        $averageSale = $salesCount > 0 ? $totalSales / $salesCount : 0;

        // Calculate yesterday's stats for comparison
        $yesterdayTotal = $yesterdaySales->sum('total_amount');
        $yesterdayCount = $yesterdaySales->count();
        $yesterdayAverage = $yesterdayCount > 0 ? $yesterdayTotal / $yesterdayCount : 0;

        // Calculate percentage changes
        $salesChange = $yesterdayTotal > 0 ? (($totalSales - $yesterdayTotal) / $yesterdayTotal) * 100 : 0;
        $countChange = $yesterdayCount > 0 ? (($salesCount - $yesterdayCount) / $yesterdayCount) * 100 : 0;
        $averageChange = $yesterdayAverage > 0 ? (($averageSale - $yesterdayAverage) / $yesterdayAverage) * 100 : 0;

        return Inertia::render('Dashboard', [
            'stats' => [
                'total_sales' => $totalSales,
                'sales_count' => $salesCount,
                'average_sale' => $averageSale,
                'sales_today' => $todaySales,
                'changes' => [
                    'sales' => round($salesChange, 1),
                    'count' => round($countChange, 1),
                    'average' => round($averageChange, 1),
                ]
            ]
        ]);
    }
} 