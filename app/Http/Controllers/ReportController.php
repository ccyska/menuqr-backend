<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Exports\SalesExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    // Dashboard penjualan
    public function dashboard(Request $request)
    {
        $admin = $request->user();

        $query = Order::where('restaurant_id', $admin->restaurant_id);

        return response()->json([
            'total_orders' => (clone $query)->count(),

            'pending_orders' => (clone $query)
                ->where('status', 'pending')
                ->count(),

            'completed_orders' => (clone $query)
                ->where('status', 'completed')
                ->count(),

            'cancelled_orders' => (clone $query)
                ->where('status', 'cancelled')
                ->count(),

            'total_sales' => (clone $query)
                ->where('status', 'completed')
                ->sum('total'),
        ]);
    }

    // Laporan penjualan
    public function sales(Request $request)
    {
        $admin = $request->user();

        $query = Order::where('restaurant_id', $admin->restaurant_id)
            ->where('status', 'completed');

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $orders = $query
            ->with(['restaurant', 'table', 'items.menu', 'promo'])
            ->latest()
            ->get();

        return response()->json([
            'total_orders' => $orders->count(),
            'total_sales' => $orders->sum('total'),
            'data' => $orders,
        ]);
    }

    // Export Excel
    public function exportExcel(Request $request)
    {
        $admin = $request->user();

        return Excel::download(
            new SalesExport($admin->restaurant_id, $request->from, $request->to),
            'laporan-penjualan.xlsx'
        );
    }

    // Export PDF
    public function exportPdf(Request $request)
    {
        $admin = $request->user();

        $query = Order::where('restaurant_id', $admin->restaurant_id)
            ->where('status', 'completed')
            ->with(['restaurant', 'table']);

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $orders = $query
            ->latest()
            ->get();

        $totalOrders = $orders->count();
        $totalSales = $orders->sum('total');

        $pdf = Pdf::loadView('reports.sales', [
            'orders' => $orders,
            'totalOrders' => $totalOrders,
            'totalSales' => $totalSales,
        ]);

        return $pdf->download('laporan-penjualan.pdf');
    }
}

