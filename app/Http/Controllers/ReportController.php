<?php

namespace App\Http\Controllers;

use App\Http\Repositories\CustomerTransactionRepository;
use Illuminate\Http\Request;
use App\Http\Repositories\SaleRepository;
use App\Http\Repositories\ExpenseRepository;

class ReportController extends Controller
{
    private SaleRepository $saleRepository;
    private ExpenseRepository $expenseRepository;
    private CustomerTransactionRepository $customerTransactionRepository;

    public function __construct(SaleRepository $saleRepository, ExpenseRepository $expenseRepository, CustomerTransactionRepository $customerTransactionRepository)
    {
        $this->saleRepository = $saleRepository;
        $this->expenseRepository = $expenseRepository;
        $this->customerTransactionRepository = $customerTransactionRepository;
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

    public function salesStatisticsByDay(Request $request)
    {
        $data =  $request->validate([
            'date' => 'date|required',
        ]);

        $date = $data['date'];
        try {
            $sales = $this->saleRepository->indexByDate($date, $date);
            $transactions = $this->customerTransactionRepository->indexByDate($date, $date);
            $response = [
                'sales' => [],
                'transactions' => [],
                'statistics' => [],
            ];
            $formattedSales = $sales->map(function ($sale) {
                return [
                    'profit' => $sale->profit,
                    'customer_name' =>  $sale->customer->name,
                    'user_name' => $sale->user->name,
                    'products' => $sale->products()->withTrashed()->get()->map(function ($product) {
                        return [
                            'name' => $product->name,
                            'quantity' => $product->pivot->quantity,
                            'total' => $product->pivot->total,
                            'cost' => $product->pivot->cost,
                            'price' => $product->pivot->unit_price,
                        ];
                    }),

                ];
            });
            $formattedTransactions = $transactions->map(function ($transaction) {
                return [
                    'amount' => $transaction->amount,
                    'customer_name' => $transaction->customer->name,
                    'user_name' => $transaction->user->name,
                    'type' => $transaction->type,
                ];
            });

            $statistics = [
                'sales_count' => $sales->count(),
                'total_profit' => $sales->sum('profit'),
                'total_sales' => $sales->sum('total_amount'),
            ];
            
            $response['statistics'] = $statistics;
            $response['sales'] = $formattedSales;
            $response['transactions'] = $formattedTransactions;
            return response()->success($response, 'statistics retrieved successfully', 200);
        } catch (\Throwable $th) {
            return response()->error($th->getMessage());
        }
    }
}
