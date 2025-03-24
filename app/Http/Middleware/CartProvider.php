<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Cart;

class CartProvider
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->user()) {
            $cart = Cart::firstOrCreate(['user_id' => $request->user()->id]);
            $request->session()->put('cart', $cart);
        }

        return $next($request);
    }
}
