<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Festival Tickets</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #28a745;
            padding-bottom: 20px;
        }
        .header h1 {
            color: #28a745;
            margin: 0;
            font-size: 28px;
        }
        .order-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 30px;
            border-left: 4px solid #28a745;
        }
        .ticket {
            border: 2px solid #28a745;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
            page-break-inside: avoid;
            background: #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .ticket-header {
            text-align: center;
            border-bottom: 1px dashed #ccc;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .ticket-header h2 {
            color: #28a745;
            margin: 0 0 10px 0;
            font-size: 24px;
        }
        .ticket-content {
            display: table;
            width: 100%;
        }
        .ticket-left {
            display: table-cell;
            width: 60%;
            vertical-align: top;
            padding-right: 20px;
        }
        .ticket-right {
            display: table-cell;
            width: 40%;
            text-align: center;
            vertical-align: top;
        }
        .qr-code {
            max-width: 160px;
            height: auto;
            border: 1px solid #ddd;
            padding: 10px;
            background: #fff;
        }
        .ticket-code {
            font-family: 'Courier New', monospace;
            font-size: 16px;
            font-weight: bold;
            background: #f8f9fa;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            display: inline-block;
            margin-top: 10px;
            letter-spacing: 1px;
        }
        .price-tag {
            background: #28a745;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: bold;
            display: inline-block;
        }
        .instructions {
            background: #e7f3ff;
            border-left: 4px solid #2196F3;
            padding: 12px;
            margin-top: 15px;
            border-radius: 0 4px 4px 0;
        }
        .instructions ul {
            margin: 0;
            padding-left: 20px;
        }
        .instructions li {
            margin: 5px 0;
        }
        .footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            color: #666;
            font-size: 12px;
        }
        .festival-emoji {
            font-size: 20px;
        }
    </style>
</head>
<body>
<div class="header">
    <h1>🎪 Haarlem Festival Tickets</h1>
    <p style="margin: 10px 0; font-size: 16px;">Your Digital Entry Tickets</p>
</div>

<div class="order-info">
    <p><strong>📋 Order #:</strong> {{ $order->id }}</p>
    <p><strong>👤 Customer:</strong> {{ $order->payment_details['customer_name'] }}</p>
    <p><strong>📧 Email:</strong> {{ $order->payment_details['customer_email'] }}</p>
    <p><strong>📅 Purchase Date:</strong> {{ $order->ordered_at->format('F j, Y g:i A') }}</p>
    <p><strong>💳 Payment ID:</strong> {{ $order->payment_details['payment_intent_id'] }}</p>
</div>

@foreach($tickets as $ticket)
    <div class="ticket">
        <div class="ticket-header">
            <h2><span class="festival-emoji">🎭</span> {{ $ticket['festival_name'] }}</h2>
            <span class="price-tag">€{{ number_format($ticket['price'], 2) }}</span>
        </div>

        <div class="ticket-content">
            <div class="ticket-left">
                <p><strong>🎫 Ticket Code:</strong></p>
                <div class="ticket-code">{{ $ticket['qr_code'] }}</div>

                <div class="instructions">
                    <p><strong>📱 Entry Instructions:</strong></p>
                    <ul>
                        <li>Present this QR code at the entrance</li>
                        <li>Keep this ticket with you during the event</li>
                        <li>This ticket is valid for one person only</li>
                        <li>No refunds or exchanges</li>
                    </ul>
                </div>
            </div>

            <div class="ticket-right">
                <p><strong>📲 Scan to Enter:</strong></p>
                <img src="{{ $ticket['qr_image'] }}" alt="QR Code for {{ $ticket['qr_code'] }}" class="qr-code">
                <p style="font-size: 12px; color: #666; margin-top: 10px;">
                    Unique QR Code<br>
                    {{ $ticket['qr_code'] }}
                </p>
            </div>
        </div>
    </div>
@endforeach

<div class="footer">
    <p><strong>Have a fantastic time at the Haarlem Festival! 🎉</strong></p>
    <p>For questions or support, contact us at: <strong>support@haarlemfestival.com</strong></p>
    <p style="margin-top: 15px;">
        This is your official ticket. Please keep it safe and accessible on your mobile device.<br>
        Generated on {{ now()->format('F j, Y g:i A') }}
    </p>
</div>
</body>
</html>
