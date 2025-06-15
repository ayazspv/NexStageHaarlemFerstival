<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Ticket;
use App\Models\Festival;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Exception\ApiErrorException;
use Barryvdh\DomPDF\Facade\Pdf;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

class PaymentController
{
    public function __construct()
    {
        try {
            $stripeSecret = env('STRIPE_SECRET');
            if (!$stripeSecret) {
                Log::error('STRIPE_SECRET not found in environment variables');
            } else {
                Stripe::setApiKey($stripeSecret);
                Log::info('Stripe initialized with API key');
            }
        } catch (\Exception $e) {
            Log::error('Error initializing Stripe: ' . $e->getMessage());
        }
    }
    
    public function createPaymentIntent(Request $request)
    {
        try {
            Log::info('Payment intent request received: ' . json_encode($request->all()));
            
            // Simple validation
            if (!$request->has('items') || !is_array($request->items) || empty($request->items)) {
                return response()->json(['error' => 'No items provided'], 400);
            }
            
            // Hardcode a small amount for testing
            $amount = 1000; // 10.00 in cents
            
            // Create a payment intent with hardcoded values to test if Stripe is working
            $intent = PaymentIntent::create([
                'amount' => $amount,
                'currency' => 'eur',
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
            ]);
            
            Log::info('Payment intent created successfully: ' . $intent->id);
            
            return response()->json([
                'clientSecret' => $intent->client_secret,
                'totalAmount' => 10.00 // Return the amount in decimal format
            ]);
        } catch (ApiErrorException $e) {
            // Stripe-specific error
            Log::error('Stripe API Error: ' . $e->getMessage());
            return response()->json(['error' => 'Stripe error: ' . $e->getMessage()], 500);
        } catch (\Exception $e) {
            // Generic error
            Log::error('Payment intent creation error: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], 500);
        }
    }
    
    public function processPayment(Request $request)
    {
        $validated = $request->validate([
            'payment_intent_id' => 'required|string',
            'customer_name' => 'required|string',
            'customer_email' => 'required|email',
            'items' => 'required|array|min:1',
            'items.*.festivalID' => 'required|integer',
            'items.*.festivalName' => 'required|string',
            'items.*.festivalQuantity' => 'required|integer|min:1',
        ]);

        try {
            Log::info('Starting payment processing for payment intent: ' . $validated['payment_intent_id']);

            // Recalculate total amount from backend prices
            $totalAmount = 0;
            $recalculatedItems = [];

            foreach ($validated['items'] as $item) {
                $festivalId = $item['festivalID'];
                $quantity = $item['festivalQuantity'];
                $price = 0;

                if ($festivalId > 0) {
                    // Standard festival ticket
                    $festival = Festival::findOrFail($festivalId);
                    $price = $festival->price;
                } else if ($festivalId == -1) {
                    // Day pass
                    $price = 50.00;
                } else if ($festivalId == -2) {
                    // Full pass
                    $price = 150.00;
                }

                $itemTotal = $price * $quantity;
                $totalAmount += $itemTotal;

                $recalculatedItems[] = [
                    'festivalID' => $item['festivalID'],
                    'festivalName' => $item['festivalName'],
                    'festivalQuantity' => $quantity,
                    'festivalCost' => $price
                ];
            }

            // Verify that payment amount matches calculation
            $paymentIntent = PaymentIntent::retrieve($validated['payment_intent_id']);
            $paymentAmount = $paymentIntent->amount / 100; // Convert cents to euros

            if (abs($paymentAmount - $totalAmount) > 0.01) {
                // Amount mismatch - potential tampering
                Log::error('Payment amount mismatch. Expected: ' . $totalAmount . ', Got: ' . $paymentAmount);
                return response()->json([
                    'success' => false,
                    'message' => 'Payment amount mismatch. Please try again.'
                ], 400);
            }

            Log::info('Payment verified successfully. Creating order...');

            // Create order and send email
            $order = $this->createOrderWithEmail($validated, $paymentIntent, $recalculatedItems);

            Log::info('Order created successfully: ' . $order->id);

            return response()->json([
                'success' => true,
                'redirect_url' => route('payment.success', ['order' => $order->id])
            ]);

        } catch (\Exception $e) {
            Log::error('Payment processing failed: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    private function createOrderWithEmail($data, $paymentIntent, $items)
    {
        Log::info('Step 1: Creating order record in database');

        // Step 1: Create order
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

        Log::info('Order created with ID: ' . $order->id);

        // Step 2: Create tickets
        Log::info('Step 2: Creating tickets');
        $this->createTickets($order, $items);

        // Step 3: Send email with PDF
        Log::info('Step 3: Sending confirmation email');
        $this->sendOrderConfirmationEmail($order);

        return $order;
    }

    private function createTickets($order, $items)
    {
        foreach ($items as $item) {
            $festival = Festival::find($item['festivalID']);

            if (!$festival) {
                Log::warning('Festival not found: ' . $item['festivalID']);
                continue;
            }

            Log::info('Creating ' . $item['festivalQuantity'] . ' tickets for festival: ' . $festival->name);

            for ($i = 0; $i < $item['festivalQuantity']; $i++) {
                $qrCode = 'TKT-' . strtoupper(Str::random(8));

                Ticket::create([
                    'order_id' => $order->id,
                    'festival_id' => $festival->id,
                    'qr_code' => $qrCode,
                    'quantity' => 1,
                    'price_per_ticket' => $item['festivalCost'],
                ]);

                Log::info('Ticket created: ' . $qrCode);
            }
        }
    }

    private function sendOrderConfirmationEmail($order)
    {
        try {
            Log::info('Starting email process for order: ' . $order->id);

            // Reload order with relationships
            $order = Order::with(['tickets.festival', 'user'])->find($order->id);

            if (!$order || $order->tickets->count() === 0) {
                Log::error('Order or tickets not found when sending email');
                return;
            }

            Log::info('Order loaded with ' . $order->tickets->count() . ' tickets');

            // Generate PDF
            Log::info('Generating PDF...');
            $pdfContent = $this->generateTicketsPDF($order);

            if (!$pdfContent) {
                Log::error('Failed to generate PDF content');
                return;
            }

            Log::info('PDF generated successfully, size: ' . strlen($pdfContent) . ' bytes');

            // Send email with PDF attachment
            $this->sendEmailWithPDF($order, $pdfContent);

        } catch (\Exception $e) {
            Log::error('Email sending failed for order ' . $order->id . ': ' . $e->getMessage());
            Log::error('Email error stack trace: ' . $e->getTraceAsString());
        }
    }

    private function generateTicketsPDF($order)
    {
        try {
            $tickets = [];

            foreach ($order->tickets as $ticket) {
                // Generate QR code data
                $qrCodeData = json_encode([
                    'ticket_code' => $ticket->qr_code,
                    'festival' => $ticket->festival->name,
                    'order_id' => $order->id,
                    'generated_at' => now()->toISOString(),
                ]);

                Log::info('Generating QR code for ticket: ' . $ticket->qr_code);

                // Create QR code
                $qrCodeObj = new QrCode($qrCodeData);
                $qrCodeObj->setSize(200);
                $qrCodeObj->setMargin(5);

                $writer = new PngWriter();
                $result = $writer->write($qrCodeObj);

                // Convert to base64
                $qrCodeBase64 = base64_encode($result->getString());

                $tickets[] = [
                    'qr_code' => $ticket->qr_code,
                    'festival_name' => $ticket->festival->name,
                    'price' => $ticket->price_per_ticket,
                    'qr_image' => 'data:image/png;base64,' . $qrCodeBase64,
                ];
            }

            Log::info('All QR codes generated, creating PDF...');

            // Generate PDF
            $pdf = Pdf::loadView('emails.ticket-pdf', [
                'order' => $order,
                'tickets' => $tickets,
            ]);

            return $pdf->output();

        } catch (\Exception $e) {
            Log::error('PDF generation failed: ' . $e->getMessage());
            return null;
        }
    }

    private function sendEmailWithPDF($order, $pdfContent)
    {
        try {
            $customerEmail = $order->payment_details['customer_email'];
            $customerName = $order->payment_details['customer_name'];

            Log::info('Sending email to: ' . $customerEmail);

            // Create temporary file for PDF
            $tempPath = storage_path('app/temp_ticket_' . $order->id . '.pdf');
            file_put_contents($tempPath, $pdfContent);

            Log::info('Temporary PDF created at: ' . $tempPath);

            // Send email using Mail::send
            Mail::send('emails.order-confirmation', ['order' => $order], function($message) use ($customerEmail, $customerName, $order, $tempPath) {
                $message->to($customerEmail, $customerName)
                    ->subject('🎪 Festival Tickets - Order #' . $order->id)
                    ->attach($tempPath, [
                        'as' => 'festival-tickets-order-' . $order->id . '.pdf',
                        'mime' => 'application/pdf'
                    ]);
            });

            // Clean up temporary file
            if (file_exists($tempPath)) {
                unlink($tempPath);
                Log::info('Temporary PDF file cleaned up');
            }

            Log::info('Email sent successfully to: ' . $customerEmail);

        } catch (\Exception $e) {
            Log::error('Failed to send email with PDF: ' . $e->getMessage());
            throw $e;
        }
    }

    public function showSuccess($orderId)
    {
        $order = Order::with(['tickets.festival', 'user'])->findOrFail($orderId);

        if ($order->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        return Inertia::render('Payment/Success', [
            'order' => $order,
            'message' => 'Payment successful! Your tickets have been generated and emailed to you.',
        ]);
    }

    public function showFailed()
    {
        return Inertia::render('Payment/Failed', [
            'message' => 'Payment failed. Please try again.',
            'error_code' => 'payment_failed',
        ]);
    }
}
