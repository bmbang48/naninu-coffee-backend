<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MaterialResource;
use App\Models\Material;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MaterialController extends Controller
{
    //
    public function index()
    {
        $materials = Material::latest()->paginate(10);

        return new MaterialResource(true, 'List Data Materials', $materials);
    }

    public function allMaterials()
    {
        $materials = Material::all();
        return response()->json($materials);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'unit' => 'required',
            'price' => 'required',
            'amount' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $material = Material::create([
            'name' => $request->name,
            'unit' => $request->unit,
            'price' => $request->price,
            'amount' => $request->amount,
        ]);

        return new MaterialResource(true, 'Data Material Berhasil Ditambahkan', $material);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $material = Material::find($id);

        return new MaterialResource(true, 'Detail Data Material', $material);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        //
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'unit' => 'required',
            'price' => 'required',
            'amount' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $material = Material::find($id);
        $material->update([
            'name' => $request->name,
            'unit' => $request->unit,
            'price' => $request->price,
            'amount' => $request->amount,
        ]);

        return new MaterialResource(true, 'Data Material berhasil diubah', $material);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $material = Material::find($id);

        $material->delete();
        return new MaterialResource(true, 'Data Berhasil Dihapus', null);
    }
}
