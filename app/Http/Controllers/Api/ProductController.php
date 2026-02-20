<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $products = Product::latest()->paginate(10);

        return new ProductResource(true, 'List Data Produk', $products);
    }

    public function productsCashier(Request $request)
    {
        $products = Product::where('product_name', 'LIKE', "%{$request->search}%")
            ->paginate(6);
        return new ProductResource(true, 'List Produk Kasir', $products);
    }

    /** 
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //

        $validator = Validator::make($request->all(), [
            'product_name' => 'required',
            'price' => 'required',
            'description' => 'required',
            'image' => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $image->storeAs('products', $image->hashName(), 'public');
        }

        $product = Product::create([
            'product_name' => $request->product_name,
            'price' => $request->price,
            'description' => $request->description,
            'image' => $image->hashName(),
        ]);

        return new ProductResource(true, 'Data Baru berhasil ditambahkan', $product);
    }


    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        //
        $product = Product::find($id);

        return new ProductResource(true, 'Detail Data Produk', $product);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        //
        $validator = Validator::make($request->all(), [
            'product_name' => 'required',
            'price' => 'required',
            'description' => 'required',
            'image' => 'nullable|image'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $product = Product::find($id);

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $image->storeAs('public/products', $image->hashName());

            Storage::delete('public/products/' . basename($product->image));
            $product->update([
                'product_name' => $request->product_name,
                'price' => $request->price,
                'description' => $request->description,
                'image' => $image->hashName()
            ]);
        } else {
            $product->update([
                'product_name' => $request->product_name,
                'price' => $request->price,
                'description' => $request->description,
            ]);
        }

        Log::info($request->all());

        return new ProductResource(true, 'Data Berhasil Diubah', $product);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
        $product = Product::find($id);

        $product->delete();

        return new ProductResource(true, 'Data Berhasil Dihapus', null);
    }
}
