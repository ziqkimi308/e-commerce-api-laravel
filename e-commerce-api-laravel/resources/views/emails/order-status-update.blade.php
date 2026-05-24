<!DOCTYPE html>
<html>

<head>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background: #4F46E5;
            color: white;
            padding: 20px;
            text align: center;
        }

        .content {
            padding: 20px;
            background: #f9fafb;
        }

        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border radius: 20px;
            font-weight: bold;
        }

        .status-pending {
            background: #FEF3C7;
            color: #92400E;
        }

        .status-processing {
            background: #DBEAFE;
            color: #1E40AF;
        }

        .status-shipped {
            background: #E0E7FF;
            color: #3730A3;
        }

        .status-delivered {
            background: #D1FAE5;
            color: #065F46;
        }

        .status-cancelled {
            background: #FEE2E2;
            color: #991B1B;
        }

        .footer {
            text-align: center;
            padding: 20px;
            color: #6b7280;
            font size: 0.9em;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>Order Status Update</h1>
        </div>
        <div class="content">
            <h2>Your order status has been updated</h2>
            <p>Hi {{ $order->shipping_name }},</p>
            <p>Your order <strong>{{ $order->order_number }}</strong> status
                has been updated.</p>
            <p>
                <span class="status-badge status-{{ $oldStatus }}">{{ ucfirst($oldStatus) }}</span>
                →
                <span class="status-badge status-{{ $newStatus }}">{{ ucfirst($newStatus) }}</span>
            </p>
            @if ($newStatus === 'shipped')
                <p>🚚 Your order has been shipped and is on its way!</p>
            @elseif($newStatus === 'delivered')
                <p>✅ Your order has been delivered. Enjoy your purchase!</p>
            @elseif($newStatus === 'cancelled')
                <p>❌ Your order has been cancelled. If you have questions,
                    please contact support.</p>
            @endif
            <p>Order Total: <strong>${{ number_format($order->total, 2) }}
                </strong></p>
        </div>
        <div class="footer">
            <p>Questions? Contact us at support@shop.com</p>
            <p>&copy; {{ date('Y') }} My Shop. All rights reserved.</p>
        </div>
    </div>
</body>

</html>
