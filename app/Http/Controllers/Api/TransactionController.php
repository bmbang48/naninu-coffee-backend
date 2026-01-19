<?php

namespace App\Http\Controllers\Api;

use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\TransactionResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $transactions = Transaction::with('items.product')->latest()->paginate(10);

        return new TransactionResource(true, 'List Data Transaksi', $transactions);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $validator = Validator::make($request->all(), [
            'transaction_date' => 'required|string',
            'transaction_code' => 'required|string',
            'customer_name' => 'required|string',
            'tax' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'total_price' => 'required|numeric|min:0',
            'pay' => 'required|numeric|min:0',
            'change' => 'required|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.subtotal' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        DB::beginTransaction();
        try {
            //simpan data transaksi
            $transaction = Transaction::create([
                'transaction_date' => $request->transaction_date,
                'transaction_code' => $request->transaction_code,
                'customer_name' => $request->customer_name,
                'tax' => $request->tax,
                'discount' => $request->discount,
                'total_price' => $request->total_price,
                'pay' => $request->pay,
                'change' => $request->change,
            ]);

            foreach ($request->items as $item) {
                $transaction->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $item['subtotal'],
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil disimpan',
                'data' => $transaction->load('items'),
            ], 201);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        //
        $transaction = Transaction::with('product')->find($id);

        if (!$transaction) {
            return response()->json(['message' => 'Data not found'], 404);
        }

        return new TransactionResource(true, 'Detail Data Transaksi', $transaction);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function update(Request $request, $id)
    {
        $transaction = Transaction::find($id);

        $validator = Validator::make($request->all(), [
            'id_product' => 'required|exists:products,id',
            'transaction_date' => 'required|date',
            'transaction_code' => 'required|string',
            'customer_name' => 'required|string',
            'quantity' => 'required|integer|min:1',
            'tax' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'total_price' => 'required|numeric|min:0',
            'pay' => 'required|numeric|min:0',
            'change' => 'required|numeric|min:0',
        ]);

        if (!$transaction) {
            return response()->json(['message' => 'Data not found'], 404);
        }

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        // dd($request->all());

        $transaction->update([
            'id_product' => $request->id_product,
            'transaction_date' => $request->transaction_date,
            'transaction_code' => $request->transaction_code,
            'customer_name' => $request->customer_name,
            'quantity' => $request->quantity,
            'tax' => $request->tax,
            'discount' => $request->discount,
            'total_price' => $request->total_price,
            'pay' => $request->pay,
            'change' => $request->change,
        ]);

        return new TransactionResource(true, 'Data berhasil diupdate', $transaction);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
        $transaction = Transaction::find($id);
        if (!$transaction) {
            return response()->json(['message' => 'Data not found'], 404);
        }

        $transaction->delete();

        return new TransactionResource(true, 'Data berhasil dihapus', null);
    }
}
