<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index()
    {
        return Product::all();
    }

    public function store(Request $request): JsonResponse
    {
        $product = Product::create($this->validatedData($request));

        return response()->json($product, 201);
    }

    public function show(Product $product): Product
    {
        return $product;
    }

    public function update(Request $request, Product $product): Product
    {
        $product->update($this->validatedData($request, true, $product));

        return $product->fresh();
    }

    public function destroy(Product $product): JsonResponse
    {
        $this->deleteImage($product);
        $product->delete();

        return response()->json(['message' => 'Product deleted']);
    }

    private function validatedData(Request $request, bool $updating = false, ?Product $product = null): array
    {
        $rules = [
            'name' => [$updating ? 'sometimes' : 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'price' => [$updating ? 'sometimes' : 'required', 'numeric', 'min:0'],
            'stock' => [$updating ? 'sometimes' : 'required', 'integer', 'min:0'],
            'category' => ['sometimes', 'nullable', 'string', 'max:255'],
            'image' => ['sometimes', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ];

        $data = $request->validate($rules);
        if ($request->hasFile('image')) {
            if ($product) {
                $this->deleteImage($product);
            }
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        return $data;
    }

    private function deleteImage(Product $product): void
    {
        if ($image = $product->getRawOriginal('image')) {
            Storage::disk('public')->delete($image);
        }
    }
}
