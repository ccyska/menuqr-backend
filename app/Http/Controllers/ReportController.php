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
    public function dashboard()
    {
        return response()->json([
            'total_orders' => Order::count(),

            'pending_orders' => Order::where('status', 'pending')->count(),

            'completed_orders' => Order::where('status', 'completed')->count(),

            'cancelled_orders' => Order::where('status', 'cancelled')->count(),

            'total_sales' => Order::where('status', 'completed')->sum('total'),
        ]);
    }

    // Laporan penjualan
    public function sales(Request $request)
    {
        $query = Order::where('status', 'completed');

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
    public function exportExcel()
    {
        return Excel::download(
            new SalesExport,
            'laporan-penjualan.xlsx'
        );
    }

    // Export PDF
    public function exportPdf(Request $request)
    {
        $query = Order::where('status', 'completed')
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