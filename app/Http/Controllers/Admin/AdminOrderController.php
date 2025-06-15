<?php

namespace App\Http\Controllers\Admin;

use App\Exports\OrdersExport;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class AdminOrderController
{
    public function show(Request $request)
    {
        $query = Order::with('user', 'tickets.festival');

        // Search functionality
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;

            $query->where(function ($q) use ($searchTerm) {
                $q->where('id', 'like', "%{$searchTerm}%")
                    ->orWhereHas('user', function ($userQuery) use ($searchTerm) {
                        $userQuery->where('firstName', 'like', "%{$searchTerm}%")
                            ->orWhere('lastName', 'like', "%{$searchTerm}%")
                            ->orWhere('email', 'like', "%{$searchTerm}%");
                    })
                    ->orWhere('status', 'like', "%{$searchTerm}%")
                    ->orWhere('total_price', 'like', "%{$searchTerm}%");
            });
        }

        // Status filter
        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        }

        // Date range filter
        if ($request->has('date_from') && !empty($request->date_from)) {
            $query->whereDate('ordered_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && !empty($request->date_to)) {
            $query->whereDate('ordered_at', '<=', $request->date_to);
        }

        // Sort by most recent orders first
        $query->orderBy('ordered_at', 'desc');

        // Pagination with 20 orders per page
        $orders = $query->paginate(20)->withQueryString();

        // Calculate statistics for all orders (not just paginated)
        $allOrdersQuery = Order::with('user', 'tickets.festival');

        // Apply same filters for statistics
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $allOrdersQuery->where(function ($q) use ($searchTerm) {
                $q->where('id', 'like', "%{$searchTerm}%")
                    ->orWhereHas('user', function ($userQuery) use ($searchTerm) {
                        $userQuery->where('firstName', 'like', "%{$searchTerm}%")
                            ->orWhere('lastName', 'like', "%{$searchTerm}%")
                            ->orWhere('email', 'like', "%{$searchTerm}%");
                    })
                    ->orWhere('status', 'like', "%{$searchTerm}%")
                    ->orWhere('total_price', 'like', "%{$searchTerm}%");
            });
        }

        if ($request->has('status') && !empty($request->status)) {
            $allOrdersQuery->where('status', $request->status);
        }

        if ($request->has('date_from') && !empty($request->date_from)) {
            $allOrdersQuery->whereDate('ordered_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && !empty($request->date_to)) {
            $allOrdersQuery->whereDate('ordered_at', '<=', $request->date_to);
        }

        $allOrders = $allOrdersQuery->get();

        return Inertia::render('Admin/Orders', [
            'orders' => $orders,
            'filters' => [
                'search' => $request->search,
                'status' => $request->status,
                'date_from' => $request->date_from,
                'date_to' => $request->date_to,
            ],
            'statistics' => [
                'total_orders' => $allOrders->count(),
                'total_tickets' => $allOrders->sum(function ($order) {
                    return $order->tickets->count();
                }),
                'total_revenue' => $allOrders->sum('total_price'),
                'unique_customers' => $allOrders->pluck('user_id')->unique()->count(),
            ]
        ]);
    }

    public function export(Request $request)
    {
        $startDate    = $request->query('start_date');
        $endDate      = $request->query('end_date');
        $festivalType = $request->query('festivalType');
        $format       = $request->query('format', 'xlsx'); // Default to xlsx

        // Validate format
        if (!in_array($format, ['xlsx', 'csv'])) {
            return response()->json(['error' => 'Invalid format. Supported formats: xlsx, csv'], 400);
        }

        $export = new OrdersExport($startDate, $endDate, $festivalType);
        $fileName = $this->generateFileName($format, $startDate, $endDate, $festivalType);

        // Export based on format using Laravel Excel
        if ($format === 'csv') {
            return Excel::download($export, $fileName, \Maatwebsite\Excel\Excel::CSV, [
                'Content-Type' => 'text/csv',
            ]);
        }

        // Default Excel export
        return Excel::download($export, $fileName);
    }

    private function generateFileName($format, $startDate = null, $endDate = null, $festivalType = null)
    {
        $timestamp = Carbon::now()->format('Y-m-d_H-i-s');
        $fileName = "orders_export_{$timestamp}";

        // Add date range to filename if provided
        if ($startDate && $endDate) {
            $start = Carbon::parse($startDate)->format('Y-m-d');
            $end = Carbon::parse($endDate)->format('Y-m-d');
            $fileName .= "_{$start}_to_{$end}";
        }

        // Add festival type to filename if provided
        if ($festivalType) {
            $fileName .= "_{$festivalType}";
        }

        return $fileName . '.' . $format;
    }
}
