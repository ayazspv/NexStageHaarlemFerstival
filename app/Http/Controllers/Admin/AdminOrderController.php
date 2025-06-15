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
    public function show()
    {
        $orders = Order::with('user')
            ->with('tickets.festival')
            ->get();

        return Inertia::render('Admin/Orders', [
            'orders' => $orders,
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
