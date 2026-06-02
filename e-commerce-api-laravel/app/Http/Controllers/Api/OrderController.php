<?php

namespace App\Http\Controllers\Api;

use App\Events\OrderPlaced;
use App\Events\OrderStatusChanged;
use App\Http\Controllers\Controller;
use App\Http\Requests\PlaceOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\OrderItems;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // query starter
        // with() is eager load alternatives of load() later
        $query = $request->user()->orders()->with('items.product');

        // filter by status
        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        // query finalized
        $orders = $query->orderBy('created_at', 'desc')->paginate(15);

        return OrderResource::collection($orders);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PlaceOrderRequest $request)
    {
        return DB::transaction(function () use ($request) {
            // request is just client raw JSON passed through FormRequest
            $data = $request->validated();

            $subtotal = 0;
            $items = [];

            // Check every entry valid or not
            foreach ($data['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);

                // Check stock
                if ($product->stock < $item['quantity']) {
                    return response()->json([
                        'success' => false,
                        'message' => "Insufficient stock for {$product->name}. Available: {$product->stock}"
                    ], 400);
                }

                // subtotal is total price
                $itemTotal = $product->price * $item['quantity'];
                $subtotal += $itemTotal;

                // Shorthand for append or extend in PHP array
                // Can also use original array_push($items, [new details])
                $items[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_sku' => $product->sku,
                    'price' => $product->price,
                    'quantity' => $item['quantity'],
                    'total' => $itemTotal
                ];
            }

            // Calculate tax and shipping
            $tax = $subtotal * 0.10; // Simplify 10% tax
            $shipping = $subtotal >= 100 ? 0 : 10; // simplify free shipping for over $100 or else $10
            $total = $subtotal + $tax + $shipping;

            // Create order query
            $order = Order::create([
                'user_id' => $request->user()->id,
                'subtotal' => $subtotal,
                'tax' => $tax,
                'shipping' => $shipping,
                'total' => $total,
                'shipping_name' => $data['shipping_name'],
                'shipping_email' => $data['shipping_email'],
                'shipping_phone' => $data['shipping_phone'],
                'shipping_address' => $data['shipping_address'],
                'shipping_city' => $data['shipping_city'],
                'shipping_state' => $data['shipping_state'],
                'shipping_zip' => $data['shipping_zip'],
                'shipping_country' => $data['shipping_country'],
                'notes'=>$data['notes'] ?? null
            ]);

            // Create order-items query
            foreach ($items as $itemData) {
                OrderItems::create(
                    array_merge($itemData, ['order_id'=>$order->id])
                );
            }

            // The reason we load() right after the OrderItems create query is
            // because those items didn't exist yet when we first created the $order.
            $order->load('items.product');

            // Fire event
            event(new OrderPlaced($order));

            return response()->json([
                'success' => true,
                'message' => 'Order placed successfully',
                'data' => new OrderResource($order)
            ], 201);
        });
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Order $order)
    {
        // Authorization
        if ($request->user()->id !== $order->user_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        // eager load because we want to return items.product too
        $order->load('items.product');

        return response()->json([
            'success' => true,
            'data' => new OrderResource($order)
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    public function updateStatus(Request $request, Order $order)
    {
        // Authorization (simplify every owner can do this)
        // In real app, only admin should be able to do this
        if ($request->user()->id !== $order->user_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        // Validate data format
        $validated = $request->validate([
            'status' =>
                'required|in:pending,processing,shipped,delivered,cancelled'
        ]);

        // Update query
        $oldStatus = $order->status;
        $order->update(['status'=>$validated['status']]);

        // Fire event
        event(new OrderStatusChanged($order, $oldStatus, $validated['status']));

        return response()->json([
            'success' => true,
            'message' => 'Order status updated successfully',
            // Usually we only need load() but since we update the status,
            // so $order was changed, hence, we need fresh()
            'data' => new OrderResource($order->fresh('items.product'))
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function cancel(Request $request, Order $order)
    {
        // Authorization (simplify every owner can do this)
        // In real app, only admin should be able to do this
        if ($request->user()->id !== $order->user_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        // Can only cancel pending or processing orders
        if (!in_array($order->status, ['pending', 'processing'])) {
            return response()->json([
                'success' => false,
                'message' => 'Order cannot be cancelled'
            ], 400);
        }

        return DB::transaction(function () use ($order) {
           // Restore stock
            // In every order, there are orderItems, in every orderItem, there is product
            foreach ($order->items as $item) {
                $product = $item->product;
                // if statement here is defensive measure against invalid product or missing product in database
                if ($product) {
                    $product->increment('stock', $item->quantity);
                }
            }

            // Update
            $oldStatus = $order->status;
            $order->update(['status'=>'cancelled']);

            // Fire event
            event(new OrderStatusChanged($order, $oldStatus, 'cancelled'));

            return response()->json([
                'success' => true,
                'message' => 'Order cancelled successfully',
                'data' => new OrderResource($order->fresh('items.product'))
            ]);
        });

    }
}
