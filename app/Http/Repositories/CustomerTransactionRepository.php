<?php

namespace App\Http\Repositories;

use App\Models\CustomerTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerTransactionRepository
{
    private CustomerTransaction $customerTransaction;
    private CustomerRepository $customerRepository;

    public function __construct(CustomerTransaction $customerTransaction, CustomerRepository $customerRepository)
    {
        $this->customerTransaction = $customerTransaction;
        $this->customerRepository = $customerRepository;
    }

    public function index()
    {
        return $this->customerTransaction::with('customer')->orderBy('id', 'desc')->paginate(15);
    }

    public function indexByCustomer($id)
    {
        return $this->customerTransaction::with('customer')->where('customer_id', $id)->orderBy('id', 'desc')->paginate(15);
    }

    public function find($id)
    {
        return $this->customerTransaction::with('customer')->findOrFail($id);
    }

    public function store(array $data)
    {
        DB::beginTransaction();
        try {
            $customerTransaction = $this->customerTransaction::create($data);
            $customer = $customerTransaction->customer;
            $this->customerRepository->updateBalance($customer, $data['amount'], $data['type']);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
        return $customerTransaction;
    }

    // update 
    public function update(CustomerTransaction $customerTransaction, array $data)
    {
        DB::beginTransaction();
        try {
            $oldAmount = $customerTransaction->amount;
            $oldType = $customerTransaction->type;

            // update customer transaction
            $customerTransaction->update($data);

            $customer = $customerTransaction->customer;
            $newType = $data['type'] ?? $customerTransaction->type;
            $reverseType = $oldType == $newType ? ($oldType == 'credit' ? 'debit' : 'credit') : $newType;
            $newAmount = $data['amount'] ?? $customerTransaction->amount;

            // delete old amount from customer balance
            $this->customerRepository->updateBalance(
                $customer,
                $oldAmount,
                $reverseType
            );

            // add new amount to customer balance
            $this->customerRepository->updateBalance(
                $customer,
                $newAmount,
                $newType
            );
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
        return $customerTransaction;
    }

    public function destroy(CustomerTransaction $customerTransaction)
    {
        DB::beginTransaction();
        try {
            $customer = $customerTransaction->customer;
            $reverseType = $customerTransaction->type == 'credit' ? 'debit' : 'credit';
            $this->customerRepository->updateBalance(
                $customer,
                $customerTransaction->amount,
                $reverseType
            );
            $customerTransaction->delete();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
        return $customerTransaction;
    }
}
