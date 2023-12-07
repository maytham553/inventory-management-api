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

    public function profit(Request $request)
    {
        $request->validate([
            'from' => 'date',
            'to' => 'date',
        ]);
        $from = $request->from;
        $to = $request->to;
        try {
            $salesProfit = $this->saleRepository->indexByDate($from, $to)->sum('profit');
            $expenses = $this->expenseRepository->indexByDate($from, $to)->sum('amount');
            $profit = $salesProfit - $expenses;
            return response()->success(['profit' => $profit], 'Profit retrieved successfully', 200);
        } catch (\Throwable $th) {
            return response()->error($th->getMessage(), $th->getCode() ?: 500);
        }
    }
}
