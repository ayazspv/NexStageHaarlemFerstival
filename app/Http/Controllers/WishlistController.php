<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Wishlist;
use App\Models\WishlistItem;

class WishlistController 
{
    public function getWishlistItems(Request $request)
    {
        $wishlistItems = WishlistItem::with('festival')->where('wishlist_id', $request->session()->get('wishlist_id'))->get();

        return response()->json($wishlistItems);
    }

    public function addToWishlist(Request $request)
    {
        $request->validate([
            'festival_id' => 'required|exists:festivals,id',
        ]);

        $wishlistId = $request->session()->get('wishlist_id');
        if (!$wishlistId) {
            $wishlist = Wishlist::create();
            $request->session()->put('wishlist_id', $wishlist->id);
            $wishlistId = $wishlist->id;
        }

        WishlistItem::firstOrCreate(
            ['wishlist_id' => $wishlistId, 'festival_id' => $request->festival_id]
        );

        return response()->json(['message' => 'Festival added to wishlist successfully']);
    }

    public function removeFromWishlist(Request $request, WishlistItem $wishlistItem)
    {
        $wishlistItem->delete();

        return response()->json(['message' => 'Wishlist item removed successfully']);
    }
}
