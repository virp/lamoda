<?php

namespace App\Http\Controllers;

use App\Exceptions\DeletingUsedInContainersProductException;
use App\Http\Requests\ProductStoreRequest;
use App\Http\Requests\ProductUpdateRequest;
use App\Http\Resources\ProductResource;
use App\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return ProductResource::collection(Product::paginate($request->input('per_page', 10)))->response();
    }

    public function show(Product $product): JsonResponse
    {
        return ProductResource::make($product)->response();
    }

    public function store(ProductStoreRequest $request): JsonResponse
    {
        $product = Product::create($request->validated());

        return ProductResource::make($product)->response();
    }

    public function update(ProductUpdateRequest $request, Product $product): JsonResponse
    {
        $product->update($request->validated());

        return ProductResource::make($product)->response();
    }

    public function destroy(Product $product): JsonResponse
    {
        try {
            $product->delete();
        } catch (DeletingUsedInContainersProductException $exception) {
            return abort(409, __('Product used in container and cannot be deleted'));
        }

        return response()->json([]);
    }
}
