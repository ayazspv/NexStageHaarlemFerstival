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
use Barryvdh\DomPDF\Facade\Pdf;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

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
            Log::error('Payment Intent Creation Failed: ' . $e->getMessage());
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
            Log::info('Starting payment processing for payment intent: ' . $validated['payment_intent_id']);

            // Verify payment with Stripe
            $paymentIntent = PaymentIntent::retrieve($validated['payment_intent_id']);

            if ($paymentIntent->status !== 'succeeded') {
                Log::error('Payment not completed. Status: ' . $paymentIntent->status);
                return response()->json([
                    'success' => false,
                    'message' => 'Payment not completed. Status: ' . $paymentIntent->status
                ], 400);
            }

            Log::info('Payment verified successfully. Creating order...');

            // Create order and send email
            $order = $this->createOrderWithEmail($validated, $paymentIntent);

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

    private function createOrderWithEmail($data, $paymentIntent)
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
        $this->createTickets($order, $data['items']);

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
