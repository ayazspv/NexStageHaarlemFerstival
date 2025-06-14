<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Festival;
use App\Models\FestivalEvent;
use App\Models\Ticket;
use Illuminate\Support\Facades\Auth;

class TicketController
{
    public function processOrder(Request $request)
    {
        // Get cart items from request
        $cartItems = $request->input('cart');
        
        // Create an order
        $order = Order::create([
            'user_id' => Auth::id(),
            'total_price' => $request->input('total'),
            'status' => 'completed',
            'ordered_at' => now(),
        ]);
        
        // Process each item in the cart
        foreach ($cartItems as $item) {
            if ($item['ticket_type'] === 'standard') {
                // Handle regular festival tickets
                $this->createStandardTicket($order->id, $item);
            } 
            else if ($item['ticket_type'] === 'day_pass') {
                // Handle day pass tickets
                $this->createDayPassTicket($order->id, $item);
            } 
            else if ($item['ticket_type'] === 'full_pass') {
                // Handle full festival pass tickets
                $this->createFullPassTicket($order->id, $item);
            }
        }
        
        // Return success response
        return response()->json([
            'success' => true,
            'order_id' => $order->id
        ]);
    }

    private function createDayPassTicket($orderId, $item)
    {
        // Create a special ticket for a specific day
        for ($i = 0; $i < $item['quantity']; $i++) {
            Ticket::create([
                'order_id' => $orderId,
                'festival_id' => 5, // Special ID for day passes
                'ticket_type' => 'day_pass',
                'day' => $item['details']['day'],
                'qr_code' => $this->generateQrCode(),
            ]);
        }
    }

    private function createFullPassTicket($orderId, $item)
    {
        // Create a special ticket for the full festival
        for ($i = 0; $i < $item['quantity']; $i++) {
            Ticket::create([
                'order_id' => $orderId,
                'festival_id' => 6, // Special ID for full passes
                'ticket_type' => 'full_pass',
                'qr_code' => $this->generateQrCode(),
            ]);
        }
    }
}