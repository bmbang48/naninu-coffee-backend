<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CashFlow;

class CashFlowController extends Controller
{
    public function index(Request $request)
    {
        $query = CashFlow::query();

        if (!empty($request->start_date)) {
            $query->whereDate('date', '>=', $request->start_date);
        }

        if (!empty($request->end_date)) {
            $query->whereDate('date', '<=', $request->end_date);
        }

        return $query->latest()->paginate(10);
    }

    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:IN,OUT',
            'amount' => 'required|numeric|min:1',
            'date' => 'required|date'
        ]);

        $cash = CashFlow::create($request->all());

        return response()->json($cash);
    }

    public function summary(Request $request)
    {
        $query = CashFlow::query();

        if (!empty($request->start_date)) {
            $query->whereDate('date', '>=', $request->start_date);
        }

        if (!empty($request->end_date)) {
            $query->whereDate('date', '<=', $request->end_date);
        }

        $cashIn = (clone $query)->where('type', 'IN')->sum('amount');
        $cashOut = (clone $query)->where('type', 'OUT')->sum('amount');

        return response()->json([
            'cash_in' => $cashIn,
            'cash_out' => $cashOut,
            'balance' => $cashIn - $cashOut
        ]);
    }

    public function chart(Request $request)
    {
        $query = CashFlow::query();

        if (!empty($request->start_date)) {
            $query->whereDate('date', '>=', $request->start_date);
        }

        if (!empty($request->end_date)) {
            $query->whereDate('date', '<=', $request->end_date);
        }

        $data = $query
            ->selectRaw("
                DATE(date) as date,
                SUM(CASE WHEN type = 'IN' THEN amount ELSE 0 END) as cash_in,
                SUM(CASE WHEN type = 'OUT' THEN amount ELSE 0 END) as cash_out
            ")
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json($data);
    }
}
