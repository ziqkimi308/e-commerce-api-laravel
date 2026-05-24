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

        .order-details {
            background: white;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }

        .item {
            padding: 10px 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .total {
            font-size: 1.2em;
            font-weight: bold;
            margin-top: 20px;
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
            <h1>Order Confirmation</h1>
        </div>
        <div class="content">
            <h2>Thank you for your order!</h2>
            <p>Hi {{ $order->shipping_name }},</p>
            <p>Your order has been received and is being processed.</p>
            <div class="order-details">
                <h3>Order Details</h3>
                <p><strong>Order Number:</strong> {{ $order->order_number }}
                </p>
                <p><strong>Order Date:</strong> {{ $order->created_at > format('M d, Y') }}</p>
                <p><strong>Status:</strong> {{ ucfirst($order->status) }}</p>
                <h3>Items:</h3>
                @foreach ($order->items as $item)
                    <div class="item">
                        <p><strong>{{ $item->product_name }}</strong></p>
                        <p>Quantity: {{ $item->quantity }} × ${{ number_format($item->price, 2) }} =
                            ${{ number_format($item->total, 2) }}</p>
                    </div>
                @endforeach
                <div class="total">
                    <p>Subtotal: ${{ number_format($order->subtotal, 2) }}</p>
                    <p>Tax: ${{ number_format($order->tax, 2) }}</p>
                    <p>Shipping: ${{ number_format($order->shipping, 2) }}</p>
                    <p><strong>Total: ${{ number_format($order->total, 2) }}
                        </strong></p>
                </div>
                <h3>Shipping Address:</h3>
                <p>
                    {{ $order->shipping_name }}<br>
                    {{ $order->shipping_address }}<br>
                    {{ $order->shipping_city }}, {{ $order->shipping_state }}
                    {{ $order->shipping_zip }}<br>
                    {{ $order->shipping_country }}
                </p>
            </div>
            <p>We'll send you another email when your order ships.</p>
        </div>
        <div class="footer">
            <p>Questions? Contact us at support@shop.com</p>
            <p>&copy; {{ date('Y') }} My Shop. All rights reserved.</p>
        </div>
    </div>
</body>

</html>
