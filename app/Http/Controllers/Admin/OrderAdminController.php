<?php

namespace App\Http\Controllers\Admin;

use App\Exports\OrdersExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class OrderAdminController
{
    public function export(Request $request)
    {
        $startDate    = $request->query('start_date');
        $endDate      = $request->query('end_date');
        $festivalType = $request->query('festivalType');

        return Excel::download(new OrdersExport($startDate, $endDate, $festivalType), 'orders.xlsx');
    }
}
