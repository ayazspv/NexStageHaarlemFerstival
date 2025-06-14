<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class CartController
{
    public function index()
    {
        return Inertia::render('Cart/Cart');
    }

    public function paymentCredentialsRender()
    {
        return Inertia::render('Cart/PaymentCredentials');
    }

    public function paymentCredentials(Request $request)
    {
        $validated = $request->validate([
            'totalAmount' => 'required|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.festivalID' => 'required|integer',
            'items.*.festivalName' => 'required|string',
            'items.*.festivalQuantity' => 'required|integer|min:1',
            'items.*.festivalCost' => 'required|numeric|min:0',
        ]);

        // Since we're using localStorage, we don't store in session
        // Just pass the data directly to the payment form
        return Inertia::render('Cart/PaymentCredentials', [
            'cartData' => $validated,
        ]);
    }
}
