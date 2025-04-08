<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\CartItem;
use Inertia\Inertia;

class CartController
{
    public function getCartItems(Request $request)
    {
        $cartItems = CartItem::with('festival')->where('cart_id', $request->session()->get('cart_id'))->get();

        return response()->json($cartItems);
    }

    public function addToCart(Request $request)
    {
        $request->validate([
            'festival_id' => 'required|exists:festivals,id',
        ]);

        $cartId = $request->session()->get('cart_id');
        if (!$cartId) {
            $cart = Cart::create();
            $request->session()->put('cart_id', $cart->id);
            $cartId = $cart->id;
        }

        $cartItem = CartItem::firstOrCreate(
            ['cart_id' => $cartId, 'festival_id' => $request->festival_id],
            ['quantity' => 0]
        );

        $cartItem->quantity += 1;
        $cartItem->save();

        return response()->json(['message' => 'Festival added to cart successfully']);
    }

    public function updateCartItem(Request $request, CartItem $cartItem)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $cartItem->quantity = $request->quantity;
        $cartItem->save();

        return response()->json(['message' => 'Cart item updated successfully']);
    }

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

        return Inertia::render('Cart/PaymentCredentials', [
            'cartData' => $validated,
        ]);
    }

}
