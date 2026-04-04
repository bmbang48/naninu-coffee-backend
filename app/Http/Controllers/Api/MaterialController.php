<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MaterialResource;
use App\Models\Material;
use App\Models\MaterialLog;
use App\Models\Transaction;
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
            'stock' => 'required',
            'amount' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $material = Material::create([
            'name' => $request->name,
            'unit' => $request->unit,
            'price' => $request->price,
            'stock' => $request->stock,
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
            'amount' => 'required',
            'stock' => 'required',
            'min_stock' => 'required'
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
            'stock' => $request->stock,
            'min_stock' => $request->min_stock
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

    //restock
    public function restock(Request $request)
    {
        $request->validate([
            'material_id' => 'required|exists:materials,id',
            'amount' => 'required|numeric|min:1',
            'note' => 'nullable|string'
        ]);

        $material = Material::findOrFail($request->material_id);

        // 🔥 tambah stock
        $material->stock += $request->amount;
        $material->save();

        // 🔥 log
        MaterialLog::create([
            'material_id' => $material->id,
            'type' => 'IN',
            'amount' => $request->amount,
            'note' => $request->note ?? 'Restock'
        ]);

        return response()->json([
            'message' => 'Stock berhasil ditambahkan'
        ]);
    }

    public function adjustStock(Request $request)
    {
        $request->validate([
            'material_id' => 'required|exists:materials,id',
            'type' => 'required|in:IN,OUT',
            'amount' => 'required|numeric|min:1',
            'note' => 'nullable|string'
        ]);

        $material = Material::findOrFail($request->material_id);

        if ($request->type === 'OUT') {

            if ($material->stock < $request->amount) {
                return response()->json([
                    'message' => 'Stock tidak cukup'
                ], 400);
            }

            $material->stock -= $request->amount;
        } else {
            $material->stock += $request->amount;
        }

        $material->save();

        // 🔥 log
        MaterialLog::create([
            'material_id' => $material->id,
            'type' => $request->type,
            'amount' => $request->amount,
            'note' => $request->note ?? 'Manual adjustment'
        ]);

        return response()->json([
            'message' => 'Stock berhasil diupdate'
        ]);
    }
    public function logs(Request $request)
    {
        $query = MaterialLog::with('material');

        if ($request->material_id) {
            $query->where('material_id', $request->material_id);
        }
        if ($request->type) {
            $query->where('type', $request->type);
        }
        if ($request->start_date) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->end_date) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $logs = $query->latest()->paginate(10);

        return response()->json($logs);
    }
    public function dashboard()
    {
        // 💰 revenue bulan ini
        $income = Transaction::whereMonth('transaction_date', now()->month)
            ->sum('total_price');

        // 📦 total stock
        $totalValueStock = 0;

        $materials = Material::all();

        foreach ($materials as $material) {
            if ($material->amount == 0) continue;
            $value = ($material->stock / $material->amount) * $material->price;
            $totalValueStock += $value;
        }

        // ⚠️ low stock
        $materials = Material::all();

        $lowStock = Material::whereColumn('stock', '<', 'min_stock')->get();

        // 🔥 most used materials
        $mostUsed = MaterialLog::selectRaw('material_id, SUM(amount) as total')
            ->where('type', 'OUT')
            ->groupBy('material_id')
            ->orderByDesc('total')
            ->with('material')
            ->limit(5)
            ->get();

        return response()->json([
            'income' => $income,
            'total_value_stock' => $totalValueStock,
            'low_stock' => $lowStock,
            'most_used' => $mostUsed
        ]);
    }

    public function lowstock()
    {
        $materials = Material::whereColumn('stock', '<', 'min_stock')->get();

        return response()->json($materials);
    }
}
