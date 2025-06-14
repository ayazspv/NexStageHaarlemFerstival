<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Order Confirmation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #28a745;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px;
        }
        .order-details {
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .ticket-item {
            border-bottom: 1px solid #eee;
            padding: 10px 0;
        }
        .ticket-item:last-child {
            border-bottom: none;
        }
        .total {
            font-weight: bold;
            font-size: 1.2em;
            color: #28a745;
            text-align: right;
            margin-top: 15px;
        }
        .footer {
            text-align: center;
            padding: 20px;
            color: #666;
            border-top: 1px solid #eee;
            margin-top: 20px;
        }
        .qr-info {
            background-color: #e7f3ff;
            border-left: 4px solid #2196F3;
            padding: 12px;
            margin: 15px 0;
        }
    </style>
</head>
<body>
<div class="header">
    <h1>🎉 Thank You for Your Order!</h1>
    <p>Order #{{ $order->id }}</p>
</div>

<div class="order-details">
    <h2>Order Details</h2>
    <p><strong>Customer:</strong> {{ $order->payment_details['customer_name'] }}</p>
    <p><strong>Email:</strong> {{ $order->payment_details['customer_email'] }}</p>
    <p><strong>Order Date:</strong> {{ $order->ordered_at->format('F j, Y g:i A') }}</p>
    <p><strong>Payment ID:</strong> {{ $order->payment_details['payment_intent_id'] }}</p>

    <h3>Your Festival Tickets</h3>
    @foreach($order->tickets as $ticket)
        <div class="ticket-item">
            <strong>🎪 {{ $ticket->festival->name }}</strong><br>
            <strong>Ticket Code:</strong> <code>{{ $ticket->qr_code }}</code><br>
            <strong>Price:</strong> €{{ number_format($ticket->price_per_ticket, 2) }}
        </div>
    @endforeach

    <div class="total">
        <p>Total Paid: €{{ number_format($order->total_price, 2) }}</p>
    </div>
</div>

<div class="qr-info">
    <h4>📱 Your QR Code Tickets</h4>
    <p>Your festival tickets are attached to this email as a <strong>PDF file</strong>. Please:</p>
    <ul>
        <li>Download and save the PDF to your phone</li>
        <li>Present the QR codes at the festival entrance</li>
        <li>Keep a backup copy in case your phone battery dies</li>
        <li>You can print the tickets if preferred</li>
    </ul>
</div>

<div class="footer">
    <p><strong>Important:</strong> Each QR code is unique and can only be used once.</p>
    <p>Have a fantastic time at the Haarlem Festival! 🎉</p>
    <hr>
    <small>
        If you have any questions, please contact us at support@haarlemfestival.com<br>
        This is an automated message, please do not reply to this email.
    </small>
</div>
</body>
</html>
