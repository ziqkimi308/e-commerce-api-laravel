<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Base query
        $query = Product::query();

        // Filter query
        if (!$request->user()) {
            $query->active(); // scopeActive()
        }

        // Filter by stock
        if ($request->boolean('in_stock')) {
            $query->inStock(); // scopeInStock()
        }

        // Filter by search
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
               $q->where('name', 'ilike', "%{$search}%")
                   ->orWhere('description', 'ilike', "%{$search}%")
                   ->orWhere('sku', 'ilike', "%{$search}%");
            });
        }

        // filter by price
        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->input('min_price'));
        }

        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->input('max_price'));
        }

        // filter by on sale only
        if ($request->boolean('on_sale')) {
            $query->whereNotNull('compare_price')
                ->whereColumn('compare_price', '>', 'price'); // direct compare 2 columns
        }

        // Sort
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // pagination
        $perPage = $request->input('per_page', 20);
        $products = $query->paginate($perPage);

        return ProductResource::collection($products);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request)
    {
        // Retrieved validated format data
        $data = $request->validated();

        // Handle image upload
        if ($request->hasFile('image')) {
            $filename = time() . '_' . uniqid() . '.' . $request->file('image')
                ->getClientOriginalExtension();
            $data['image'] = $request->file('image')->storeAs('products', $filename, 'public');
        }

        // Create query
        $product = Product::create($data);

        return response()->json([
            'success'=>true,
            'message'=>'Product created successfully',
            'data'=>new ProductResource($product)
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $slug)
    {
        // Fetch query
        $product = Product::where('slug', $slug)->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => new ProductResource($product)
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        // Retrieved validated data format
        $data = $request->validated();

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image
            // has(), input(), hasFile() only used on Request object
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }

            $filename = time(). '_' . uniqid() . '.' . $request->file('image')->getClientOriginalExtension();
            $data['image'] = $request->file('image')->storeAs('products', $filename, 'public');
        }

        // Update query
        $product->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully',
            'data' => new ProductResource($product->fresh())
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        // Delete image
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        // Delete query
        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully'
        ]);
    }
}
