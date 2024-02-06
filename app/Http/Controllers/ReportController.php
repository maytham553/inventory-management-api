<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Repositories\SaleRepository;
use App\Http\Repositories\ExpenseRepository;

class ReportController extends Controller
{
    private SaleRepository $saleRepository;
    private ExpenseRepository $expenseRepository;

    public function __construct(SaleRepository $saleRepository, ExpenseRepository $expenseRepository)
    {
        $this->saleRepository = $saleRepository;
        $this->expenseRepository = $expenseRepository;
    }

    public function salesStatistics(Request $request)
    {
        $request->validate([
            'from' => 'date|nullable',
            'to' => 'date|nullable',
        ]);
        $from = $request->from;
        $to = $request->to;
        try {
            $sales = $this->saleRepository->indexByDate($from, $to);
            $salesProfit = $sales->sum('profit');
            $totalSales = $sales->sum('total_amount');
            $expenses = $this->expenseRepository->indexByDate($from, $to)->sum('amount');
            $profit = $salesProfit - $expenses;
            $response = [
                'sales_profit' => $salesProfit,
                'total_sales' => $totalSales,
                'expenses' => $expenses,
                'profit' => $profit,
            ];
            return response()->success($response, 'statistics retrieved successfully', 200);
        } catch (\Throwable $th) {
            return response()->error($th->getMessage(), $th->getCode() ?: 500);
        }
    }
}
