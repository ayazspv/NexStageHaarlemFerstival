<?php

namespace App\Http\Controllers\Admin;

use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminOrderController
{
    public function show()
    {
        $orders = Order::all();

        return Inertia::render('Admin/Orders', [
            'orders' => $orders,
        ]);
    }
}
