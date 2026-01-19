<?php

namespace App\Http\Controllers\Api;

use App\Models\OtherCosts;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\OtherCostResource;
use Illuminate\Support\Facades\Validator;

class OtherCostsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $otherCosts = OtherCosts::latest()->paginate(10);

        return new OtherCostResource(true, 'Menampilkan data Other Costs', $otherCosts);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $validator = Validator::make($request->all(), [
            'name_cost' => 'required',
            'cost_per_product' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->fails(), 422);
        }

        $otherCost = OtherCosts::create([
            'name_cost' => $request->name_cost,
            'cost_per_product' => $request->cost_per_product
        ]);

        return new OtherCostResource(true, 'Data baru berhasil ditambahkan', $otherCost);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        //
        $otherCost = OtherCosts::find($id);

        return new OtherCostResource(true, 'Menampilkan detail cost', $otherCost);
    }



    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        //
        $otherCost = OtherCosts::find($id);

        $validator = Validator::make($request->all(), [
            'name_cost' => 'required',
            'cost_per_product' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->fails(), 422);
        }

        $otherCost->update([
            'name_cost' => $request->name_cost,
            'cost_per_product' => $request->cost_per_product,
        ]);

        return new OtherCostResource(true, 'Data Berhasil Diubah', $otherCost);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
        $otherCost = OtherCosts::find($id);
        $otherCost->delete();

        return new OtherCostResource(true, 'Data Berhasil Dihapus', null);
    }
}
