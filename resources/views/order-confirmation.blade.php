<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Order Confirmation</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #f8f9fa; padding: 20px; text-align: center; }
        .order-details { background-color: #ffffff; border: 1px solid #ddd; padding: 20px; margin: 20px 0; }
        .ticket-item { border-bottom: 1px solid #eee; padding: 10px 0; }
        .ticket-item:last-child { border-bottom: none; }
        .total { font-weight: bold; font-size: 1.2em; color: #28a745; }
        .footer { text-align: center; padding: 20px; color: #666; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>Thank You for Your Order!</h1>
        <p>Order #{{ $order->id }}</p>
    </div>

    <div class="order-details">
        <h2>Order Details</h2>
        <p><strong>Customer:</strong> {{ $order->payment_details['customer_name'] }}</p>
        <p><strong>Email:</strong> {{ $order->payment_details['customer_email'] }}</p>
        <p><strong>Order Date:</strong> {{ $order->ordered_at->format('F j, Y g:i A') }}</p>
        <p><strong>Transaction ID:</strong> {{ $order->payment_details['transaction_id'] }}</p>

        <h3>Your Tickets</h3>
        @foreach($order->tickets as $ticket)
            <div class="ticket-item">
                <strong>{{ $ticket->festival->name }}</strong><br>
                Ticket Code: {{ $ticket->qr_code }}<br>
                Price: €{{ number_format($ticket->price_per_ticket, 2) }}
            </div>
        @endforeach

        <div class="total">
            <p>Total: €{{ number_format($order->total_price, 2) }}</p>
        </div>
    </div>

    <div class="footer">
        <p>Your QR codes are attached to this email. Please present them at the festival entrance.</p>
        <p>Thank you for choosing our festivals!</p>
    </div>
</div>
</body>
</html>
