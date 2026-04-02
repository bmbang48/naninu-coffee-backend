<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\OtherCosts;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    //


    public function cashflow(Request $request)
    {
        try {

            $query = Transaction::query();

            if (!empty($request->start_date)) {
                $query->whereDate('transaction_date', '>=', $request->start_date);
            }

            if (!empty($request->end_date)) {
                $query->whereDate('transaction_date', '<=', $request->end_date);
            }

            $income = $query->sum('total_price');
            $hpp = $query->sum('total_hpp');

            // 🔥 DETEKSI MODE
            $isMonthly = false;

            if (!empty($request->start_date) && !empty($request->end_date)) {
                $start = Carbon::parse($request->start_date)->startOfDay();
                $end = Carbon::parse($request->end_date)->endOfDay();

                $isMonthly = $start->isSameDay($start->copy()->startOfMonth()) &&
                    $end->isSameDay($end->copy()->endOfMonth());
            }
            $otherCost = 0;

            // ✅ ALL TIME
            if (empty($request->start_date) && empty($request->end_date)) {
                $otherCost = OtherCosts::sum('amount');
            }

            // ✅ MONTH ONLY
            else if ($isMonthly) {
                $otherCost = OtherCosts::whereBetween('created_at', [
                    $request->start_date . ' 00:00:00',
                    $request->end_date . ' 23:59:59'
                ])->sum('amount');
            }

            // ❌ DAILY / WEEK → tetap 0

            return response()->json([
                'income' => $income,
                'hpp' => $hpp,
                'other_cost' => $otherCost,
                'net_profit' => $income - $hpp - $otherCost
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'line' => $e->getLine()
            ], 500);
        }
    }
}
