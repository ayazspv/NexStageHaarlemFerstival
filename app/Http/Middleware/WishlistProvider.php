<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Wishlist;

class WishlistProvider
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->user()) {
            $wishlist = Wishlist::firstOrCreate(['user_id' => $request->user()->id]);
            $request->session()->put('wishlist', $wishlist);
        }

        return $next($request);
    }
}
