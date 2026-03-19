<?php

namespace App\Http\Controllers\Api;

use App\Models\RecipeProduct;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\RecipeProductResource;
use App\Models\Product;
use Illuminate\Support\Facades\Validator;

class RecipeProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $recipe = RecipeProduct::with(['product', 'material'])->latest()->get();

        return new RecipeProductResource(true, 'Menampilkan Data Resep', $recipe);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $validator = Validator::make($request->all(), [
            'id_product' => 'required|exists:products,id',
            'materials' => 'required|array',
            'materials.*.id_material' => 'required|exists:materials,id',
            'materials.*.amount_used' => 'required|numeric|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $recipes = [];

        foreach ($request->materials as $material) {
            $recipe = RecipeProduct::create([
                'id_product' => $request->id_product,
                'id_material' => $material['id_material'],
                'amount_used' => $material['amount_used']
            ]);

            $recipeWithRelations = RecipeProduct::with(['product', 'material'])->find($recipe->id);
            $recipes[] = $recipeWithRelations;
        }


        return new RecipeProductResource(true, 'Data resep baru berhasil ditambahkan', $recipes);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        //
        $product = Product::find($id);
        $recipe = RecipeProduct::where('id_product', $id)->first();

        return new RecipeProductResource(true, 'Menampilkan detail recipe', $recipe);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function update(Request $request, $id_product)
    {
        //

        $recipe = RecipeProduct::find($id_product);

        $validator = Validator::make($request->all(), [
            'materials' => 'required|array',
            'materials.*.id_material' => 'required|exists:materials,id',
            'materials.*.amount_used' => 'required|numeric|min:1'
        ]);

        RecipeProduct::where('id_product', $id_product)->delete();

        if ($validator->fails()) {
            return response()->json($validator->fails(), 422);
        }

        $recipes = [];

        foreach ($request->materials as $material) {
            $recipe = RecipeProduct::create([
                'id_product' => $id_product,
                'id_material' => $material['id_material'],
                'amount_used' => $material['amount_used']
            ]);

            $recipeWithRelations = RecipeProduct::with(['product', 'material'])->find($recipe->id);
            $recipes[] = $recipeWithRelations;
        }


        return new RecipeProductResource(true, 'Data berhasil diubah', $recipes);
    }

    /**
     * Update the specified resource in storage.
     */

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
        $product = Product::find($id);
        $recipe = RecipeProduct::where('id_product', $product->id);

        $recipe->delete();

        return new RecipeProductResource(true, 'Data Berhasil dihapus', null);
    }

    public function getByProduct(Request $request)
    {

        $search = strtolower(trim($request->search ?? ''));
        $keywords = explode(' ', $search);

        $product = Product::with('materials')->get()->first(function ($item) use ($keywords) {
            $name = strtolower($item->product_name);

            foreach ($keywords as $word) {
                if (!str_contains($name, $word)) {
                    return false;
                }
            }

            return true;
        });
        // dd(Product::pluck('product_name'));

        // dd($search, Product::pluck('product_name'));    
        if (!$product) {
            return response()->json([
                'message' => 'Produk tidak ditemukan'
            ], 404);
        }
        // dd($product->materials);

        $materials = $product->materials->map(function ($material) {
            return [
                'name' => $material->name,
                'amount' => $material->pivot->amount_used ?? 0,
                'unit' => $material->unit,
            ];
        });


        return response()->json([
            'product' => $product->product_name,
            'materials' => $materials
        ]);
    }
}
