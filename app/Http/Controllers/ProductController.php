<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Http\Resources\UserResource;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return ProductResource::collection(Product::all());
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return mixed
     */
    public function store(Request $request)
    {
        $user = auth()->user();

        if($user->role=='seller') {
            $data = $request->validate([
                'product_name' => 'required|string|max:255',
                'cost' => 'required|integer',
                'amount_available' => 'required|integer',
                'seller_id' => 'integer|exists:users,id',
            ]);
        }
        return Product::create($data);
    }

    /**
     * @param $product
     *
     * @return \App\Http\Resources\ProductResource
     */
    public function show($product)
    {
        return new ProductResource($product);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param $product
     *
     * @return \App\Http\Resources\ProductResource
     */
    public function update(Request $request, $product)
    {
        $data = $request->validate([
            'product_name' => 'sometimes|required|string|max:255',
            'cost' => 'sometimes|required|integer',
            'amount_available' => 'sometimes|required|integer',
            'seller_id' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('users', 'id')->where('role', 'seller'),
            ],
        ]);

        return new ProductResource(tap($product)->update($data));
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \App\Http\Resources\ProductResource
     */
    public function buy(Request $request)
    {
        $user = auth()->user();
        $data = $request->validate([
            'product_id' => 'integer|exists:products,id',
        ]);

        $product = Product::findOrFail($data['product_id']);
        if ($user->deposit >= $product->cost) {
            $product->amount_available--;
            $user->deposit -= $product->cost;
        }
        new UserResource(tap($user)->save());

        return new ProductResource(tap($product)->save());
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function remove(Request $request)
    {
        $user = auth()->user();
        $product = Product::findOrFail($request->validate([
            'product_id' => 'integer|exists:products,id',
        ]));
        if ($user->role == 'seller') {
            $product->each->delete();
        }

        return response()->noContent();
    }
}
