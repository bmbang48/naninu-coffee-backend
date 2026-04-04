<?php

namespace App\Http\Controllers\Api;

use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\CashFlow;
use App\Http\Resources\TransactionResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Services\HppService;
use App\Services\MaterialStockService;


class TransactionController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $transactions = Transaction::with('items.product')->orderBy('transaction_date', 'desc')->get();

        return new TransactionResource(true, 'List Data Transaksi', $transactions);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $rules = [
            'transaction_date' => 'required|string',
            'transaction_code' => 'required|string',
            'customer_name' => 'required|string',
            'tax' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'total_price' => 'required|numeric|min:0',
            'order_method' => 'required|string',
            'payment_method' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.subtotal' => 'required|numeric|min:0',
        ];

        // 🔥 dynamic rules
        if ($request->payment_method === 'Cash') {
            $rules['pay'] = 'required|numeric|min:0';
            $rules['change'] = 'required|numeric|min:0';
        } else {
            $rules['pay'] = 'nullable|numeric|min:0';
            $rules['change'] = 'nullable|numeric|min:0';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        DB::beginTransaction();

        $hppService = new HppService();
        $stockService = new MaterialStockService();
        $totalHpp = 0;
        try {
            //simpan data transaksi
            $transaction = Transaction::create([
                'transaction_date' => $request->transaction_date,
                'transaction_code' => $request->transaction_code,
                'customer_name' => $request->customer_name,
                'tax' => $request->tax,
                'discount' => $request->discount,
                'total_price' => $request->total_price,
                'order_method' => $request->order_method,
                'payment_method' => $request->payment_method,
                'pay' => $request->pay,
                'change' => $request->change,
            ]);

            foreach ($request->items as $item) {

                // 🔥 1. VALIDASI STOCK DULU
                $stockService->reduceStock(
                    $item['product_id'],
                    $item['quantity'],
                    $transaction->transaction_code,
                );

                // 🔥 2. HITUNG HPP
                $hpp = $hppService->calculate($item['product_id']);
                $subtotalHpp = $hpp * $item['quantity'];

                $totalHpp += $subtotalHpp;

                // 🔥 3. BARU SIMPAN ITEM
                $transaction->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'hpp' => $hpp,
                    'subtotal_hpp' => $subtotalHpp,
                    'price' => $item['price'],
                    'subtotal' => $item['subtotal'],
                ]);
            }
            $transaction->update([
                'total_hpp' => $totalHpp,
                'total_profit' => $transaction->total_price - $totalHpp
            ]);

            CashFlow::create([
                'type' => 'IN',
                'amount' => $transaction->total_price,
                'category' => 'transaction',
                'note' => 'Transaction ' . $transaction->transaction_code,
                'date' => now()
            ]);
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil disimpan',
                'data' => $transaction->load('items'),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            $decoded = json_decode($e->getMessage(), true);

            return response()->json([
                'success' => false,
                'message' => 'Stock tidak cukup',
                'errors' => $decoded ?? [$e->getMessage()]
            ], 400);
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

    public function profitToday()
    {
        // 1. Hitung total profit hari ini
        $date = Transaction::whereDate('transaction_date', today())->select('transaction_date')->first();
        $totalPrice = Transaction::whereDate('transaction_date', today())->sum('total_price');
        $totalProfit = Transaction::whereDate('transaction_date', today())->sum('total_profit');
        $qtyToday = DB::table('transaction_items')
            ->join('transactions', 'transactions.id', '=', 'transaction_items.transaction_id')
            ->whereDate('transaction_date', today())->sum('quantity');

        // 2. Ambil produk terlaris dengan join ke tabel products
        $bestProduct = DB::table('transaction_items')
            ->join('transactions', 'transactions.id', '=', 'transaction_items.transaction_id')
            ->join('products', 'products.id', '=', 'transaction_items.product_id') // Join ke tabel products
            ->whereDate('transactions.transaction_date', today())
            ->select(
                'products.product_name as product_name', // Ambil nama dari tabel products
                DB::raw('SUM(transaction_items.quantity) as total_sold')
            )
            ->groupBy('products.id', 'products.product_name')
            ->orderByDesc('total_sold')
            ->first();

        return response()->json([
            'total_profit'  => (int) $totalProfit,
            'total_price'  => (int) $totalPrice,
            'best_product'  => $bestProduct ? $bestProduct->product_name : 'Belum ada penjualan',
            'qty_sold'      => $bestProduct ? (int) $bestProduct->total_sold : 0,
            'qty_today'      => $qtyToday,
            'date'          => $date ? $date : today()
        ]);
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
