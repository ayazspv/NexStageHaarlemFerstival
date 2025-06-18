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
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.festival_id' => 'required|integer',
            'items.*.quantity' => 'required|integer|min:1|max:10',
            'items.*.ticket_type' => 'nullable|string',
            'items.*.event_id' => 'nullable|integer',
        ]);

        try {
            $totalAmount = 0;
            $calculatedItems = [];

            // Calculate server-side prices for security
            foreach ($validated['items'] as $item) {
                $festivalId = $item['festival_id'];
                $quantity = $item['quantity'];
                $price = 0;

                // Check if this is a jazz event ticket
                if (isset($item['ticket_type']) && $item['ticket_type'] === 'jazz_event' && isset($item['event_id'])) {
                    // Get jazz event price from database
                    $jazzEvent = \App\Models\JazzFestival::where('id', $item['event_id'])->first();
                    
                    if ($jazzEvent) {
                        // Use artist's specific ticket price
                        $price = $jazzEvent->ticket_price ?? 20.00;
                    } else {
                        // Fallback to festival price
                        $festival = Festival::find($festivalId);
                        $price = $festival->price ?? 20.00;
                    }
                } else if ($festivalId > 0) {
                    // Standard festival ticket
                    $festival = Festival::find($festivalId);
                    $price = $festival->price ?? 20.00;
                } else if ($festivalId == -1) {
                    // Day pass
                    $price = 50.00;
                } else if ($festivalId == -2) {
                    // Full pass
                    $price = 150.00;
                }

                $itemTotal = $price * $quantity;
                $totalAmount += $itemTotal;

                // Add artist_name for jazz events
                $itemData = [
                    'festival_id' => $festivalId,
                    'festival_name' => isset($item['ticket_type']) && $item['ticket_type'] === 'jazz_event' ? 
                        ($item['artist_name'] ?? 'Jazz Artist') : 
                        (Festival::find($festivalId)->name ?? 'Festival Ticket'),
                    'quantity' => $quantity,
                    'price_per_ticket' => $price,
                    'item_total' => $itemTotal,
                ];

                // Add jazz event specific data
                if (isset($item['ticket_type']) && $item['ticket_type'] === 'jazz_event') {
                    $itemData['ticket_type'] = 'jazz_event';
                    $itemData['event_id'] = $item['event_id'] ?? null;
                }

                $calculatedItems[] = $itemData;
            }

            // Validate total amount
            if ($totalAmount <= 0 || $totalAmount > 10000) {
                return response()->json(['error' => 'Invalid total amount calculated.'], 400);
            }

            $amountInCents = (int) ($totalAmount * 100);

            Log::info('Creating payment intent', [
                'user_id' => auth()->id(),
                'total_amount' => $totalAmount,
                'calculated_items' => $calculatedItems
            ]);

            $paymentIntent = PaymentIntent::create([
                'amount' => $amountInCents,
                'currency' => 'eur',
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
                'metadata' => [
                    'user_id' => auth()->id(),
                    'items_count' => count($calculatedItems),
                    'calculated_total' => $totalAmount,
                ],
            ]);

            // Store validation data in session for later verification
            session(['payment_validation_' . $paymentIntent->id => [
                'calculated_items' => $calculatedItems,
                'total_amount' => $totalAmount,
                'user_id' => auth()->id(),
                'created_at' => now()->toISOString(),
            ]]);

            return response()->json([
                'client_secret' => $paymentIntent->client_secret,
                'payment_intent_id' => $paymentIntent->id,
                'calculated_total' => $totalAmount,
                'calculated_items' => $calculatedItems,
            ]);

        } catch (\Exception $e) {
            Log::error('Payment Intent Creation Failed: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to create payment intent. Please try again.'], 500);
        }
    }
    
    public function processPayment(Request $request)
    {
        $validated = $request->validate([
            'payment_intent_id' => 'required|string',
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

            // Get stored validation data from session
            $validationKey = 'payment_validation_' . $validated['payment_intent_id'];
            $storedData = session($validationKey);

            if (!$storedData) {
                Log::error('Payment validation data not found in session');
                return response()->json([
                    'success' => false,
                    'message' => 'Payment validation failed. Please try again.'
                ], 400);
            }

            // Verify user owns this payment
            if ($storedData['user_id'] !== auth()->id()) {
                Log::error('User mismatch in payment processing');
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized payment processing attempt.'
                ], 403);
            }

            // Verify payment amount matches what we calculated
            $expectedAmountInCents = (int) ($storedData['total_amount'] * 100);
            if ($paymentIntent->amount !== $expectedAmountInCents) {
                Log::error('Payment amount mismatch detected');
                return response()->json([
                    'success' => false,
                    'message' => 'Payment amount verification failed.'
                ], 400);
            }

            Log::info('Payment verified successfully. Creating order...');

            // Create order using stored validation data
            $order = $this->createOrderWithEmail($validated, $paymentIntent, $storedData);

            // Clear validation data from session
            session()->forget($validationKey);
            Log::info('Order created successfully: ' . $order->id);

            return response()->json([
                'success' => true,
                'redirect_url' => route('payment.success', ['order' => $order->id])
            ]);

        } catch (\Exception $e) {
            Log::error('Payment processing failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred during payment processing. Please try again.'
            ], 500);
        }
    }

    private function createOrderWithEmail($data, $paymentIntent, $storedData)
    {
        Log::info('Step 1: Creating order record in database');

        // Create order using server-calculated amounts
        $order = Order::create([
            'user_id' => auth()->id(),
            'total_price' => $storedData['total_amount'],
            'status' => 'completed',
            'ordered_at' => now(),
            'payment_details' => [
                'payment_intent_id' => $paymentIntent->id,
                'stripe_amount' => $paymentIntent->amount,
                'calculated_amount' => $storedData['total_amount'],
                'customer_name' => $data['customer_name'],
                'customer_email' => $data['customer_email'],
                'payment_date' => now()->toISOString(),
            ],
        ]);

        Log::info('Order created with ID: ' . $order->id);

        // Create tickets using stored validation data
        Log::info('Step 2: Creating tickets');
        $this->createTickets($order, $storedData['calculated_items']);

        // Send email with PDF
        Log::info('Step 3: Sending confirmation email');
        $this->sendOrderConfirmationEmail($order);

        return $order;
    }

    private function createTickets($order, $calculatedItems)
    {
        foreach ($calculatedItems as $item) {
            // Determine which type of ticket to create
            if ($item['ticket_type'] === 'jazz_event') {
                // Create a specific jazz event ticket
                for ($i = 0; $i < $item['quantity']; $i++) {
                    $qrCode = 'TKT-' . strtoupper(Str::random(8));
                    
                    Ticket::create([
                        'order_id' => $order->id,
                        'festival_id' => $item['festival_id'],
                        'event_id' => $item['event_id'],
                        'qr_code' => $qrCode,
                        'quantity' => 1,
                        'price_per_ticket' => $item['price_per_ticket'],
                        'ticket_type' => 'jazz_event',
                        'ticket_details' => json_encode([
                            'artist_name' => $item['artist_name'],
                            'performance_day' => $item['performance_day'],
                            'performance_time' => $item['performance_time']
                        ]),
                    ]);
                    
                    Log::info('Jazz event ticket created: ' . $qrCode);
                }
            } else {
                // Handle other ticket types (standard, day_pass, full_pass)
                $festival = Festival::find($item['festival_id']);

                if (!$festival) {
                    Log::warning('Festival not found during ticket creation: ' . $item['festival_id']);
                    continue;
                }

                Log::info('Creating ' . $item['quantity'] . ' tickets for festival: ' . $festival->name);

                for ($i = 0; $i < $item['quantity']; $i++) {
                    $qrCode = 'TKT-' . strtoupper(Str::random(8));

                    Ticket::create([
                        'order_id' => $order->id,
                        'festival_id' => $festival->id,
                        'qr_code' => $qrCode,
                        'quantity' => 1,
                        'price_per_ticket' => $item['price_per_ticket'],
                    ]);

                    Log::info('Ticket created: ' . $qrCode);
                }
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
            Log::info('Payment details: ' . json_encode($order->payment_details));

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
            // Don't throw the exception, just log it so the order still completes
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
            // Get customer details from payment_details array
            $paymentDetails = $order->payment_details;
            $customerEmail = $paymentDetails['customer_email'] ?? null;
            $customerName = $paymentDetails['customer_name'] ?? null;

            if (!$customerEmail) {
                Log::error('Customer email not found in payment details for order: ' . $order->id);
                Log::info('Payment details structure: ' . json_encode($paymentDetails));
                return;
            }

            Log::info('Sending email to: ' . $customerEmail);

            // Create temporary file for PDF
            $tempPath = storage_path('app/temp_ticket_' . $order->id . '.pdf');
            file_put_contents($tempPath, $pdfContent);

            Log::info('Temporary PDF created at: ' . $tempPath);

            // Send email using Mail::send
            Mail::send('emails.order-confirmation', ['order' => $order], function($message) use ($customerEmail, $customerName, $order, $tempPath) {
                $message->to($customerEmail, $customerName)
                    ->subject('🎪 Festival Tickets - Order #' . $order->id);

                // Check if PDF file exists before attaching
                if (file_exists($tempPath)) {
                    $message->attach($tempPath, [
                        'as' => 'festival-tickets-order-' . $order->id . '.pdf',
                        'mime' => 'application/pdf'
                    ]);
                }
            });

            // Clean up temporary file
            if (file_exists($tempPath)) {
                unlink($tempPath);
                Log::info('Temporary PDF file cleaned up');
            }

            Log::info('Email sent successfully to: ' . $customerEmail);

        } catch (\Exception $e) {
            Log::error('Failed to send email with PDF: ' . $e->getMessage());
            Log::error('Email error stack trace: ' . $e->getTraceAsString());
            // Don't throw the exception, just log it so the order still completes
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
