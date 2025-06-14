<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Ticket;
use App\Models\Festival;
use Inertia\Inertia;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderConfirmation;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Exception\CardException;

class PaymentController
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    public function createPaymentIntent(Request $request)
    {
        $validated = $request->validate([
            'totalAmount' => 'required|numeric|min:0',
            'items' => 'required|array|min:1',
        ]);

        try {
            $amountInCents = (int) ($validated['totalAmount'] * 100);

            $paymentIntent = PaymentIntent::create([
                'amount' => $amountInCents,
                'currency' => 'eur',
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
                'metadata' => [
                    'user_id' => auth()->id(),
                    'items_count' => count($validated['items']),
                ],
            ]);

            return response()->json([
                'client_secret' => $paymentIntent->client_secret,
                'payment_intent_id' => $paymentIntent->id,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to create payment intent: ' . $e->getMessage()
            ], 500);
        }
    }

    public function processPayment(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email',
            'payment_intent_id' => 'required|string',
            'totalAmount' => 'required|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.festivalID' => 'required|integer',
            'items.*.festivalName' => 'required|string',
            'items.*.festivalQuantity' => 'required|integer|min:1',
            'items.*.festivalCost' => 'required|numeric|min:0',
        ]);

        try {
            $paymentIntent = PaymentIntent::retrieve($validated['payment_intent_id']);

            if ($paymentIntent->status !== 'succeeded') {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment not completed. Status: ' . $paymentIntent->status
                ], 400);
            }

            $order = $this->createOrder($validated, $paymentIntent);

            return response()->json([
                'success' => true,
                'redirect_url' => route('payment.success', ['order' => $order->id])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    private function createOrder($data, $paymentIntent)
    {
        $order = Order::create([
            'user_id' => auth()->id(),
            'total_price' => $data['totalAmount'],
            'status' => 'completed',
            'ordered_at' => now(),
            'payment_details' => [
                'payment_intent_id' => $paymentIntent->id,
                'customer_name' => $data['customer_name'],
                'customer_email' => $data['customer_email'],
                'payment_date' => now()->toISOString(),
            ],
        ]);

        foreach ($data['items'] as $item) {
            $festival = Festival::find($item['festivalID']);

            if (!$festival) continue;

            for ($i = 0; $i < $item['festivalQuantity']; $i++) {
                $qrCode = 'TKT-' . strtoupper(Str::random(8));

                Ticket::create([
                    'order_id' => $order->id,
                    'festival_id' => $festival->id,
                    'qr_code' => $qrCode,
                    'quantity' => 1,
                    'price_per_ticket' => $item['festivalCost'],
                ]);
            }
        }

        return $order;
    }

    public function showSuccess($orderId)
    {
        $order = Order::with(['tickets.festival', 'user'])->findOrFail($orderId);

        if ($order->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        return Inertia::render('Payment/Success', [
            'order' => $order,
            'message' => 'Payment successful! Your tickets have been generated.',
        ]);
    }
}
