<?php

namespace App\Http\Controllers;

use App\Http\Repositories\CustomerRepository;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    private CustomerRepository $customerRepository;

    public function __construct(CustomerRepository $customerRepository)
    {
        $this->customerRepository = $customerRepository;
    }


    public function index()
    {
        try {
            $customers = $this->customerRepository->index();
            return response()->success($customers, 'Customers retrieved successfully', 200);
        } catch (\Throwable $th) {
            return response()->error($th->getMessage(), $th->getCode() ?: 500);
        }
    }

    public function indexByGovernorate($id)
    {
        try {
            $customers = $this->customerRepository->indexByGovernorate($id);
            return response()->success($customers, 'Customers retrieved successfully', 200);
        } catch (\Throwable $th) {
            return response()->error($th->getMessage(), $th->getCode() ?: 500);
        }
    }


    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'email' => 'nullable|email',
            'phone' => 'required|string',
            'address' => 'required|string',
            'governorate_id' => 'required|exists:governorates,id',
            'note' => 'nullable|string',
        ]);
        try {
            $customer = $this->customerRepository->store($data);
            return response()->success($customer, 'Customer created successfully', 201);
        } catch (\Throwable $th) {
            return response()->error($th->getMessage(), $th->getCode() ?: 500);
        }
    }

    public function show($id)
    {
        try {
            $customer = $this->customerRepository->find($id);
            return response()->success($customer, 'Customer retrieved successfully', 200);
        } catch (\Throwable $th) {
            return response()->error($th->getMessage(), $th->getCode() ?: 500);
        }
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'name' => 'nullable|string',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'governorate_id' => 'nullable|exists:governorates,id',
            'note' => 'nullable|string',
        ]);
        try {
            $customer = $this->customerRepository->find($id);
            $this->customerRepository->update($customer, $data);
            return response()->success($customer, 'Customer updated successfully', 200);
        } catch (\Throwable $th) {
            return response()->error($th->getMessage(), $th->getCode() ?: 500);
        }
    }

    public function reCalculateBalance($id)
    {
        try {
            $customer = $this->customerRepository->find($id);
            $this->customerRepository->reCalculateBalance($customer);
            return response()->success($customer, 'Customer balance recalculated successfully', 200);
        } catch (\Throwable $th) {
            return response()->error($th->getMessage(), $th->getCode() ?: 500);
        }
    }


    public function destroy($id)
    {
        try {
            $customer = $this->customerRepository->find($id);
            $this->customerRepository->destroy($customer);
            return response()->success($customer, 'Customer deleted successfully', 200);
        } catch (\Throwable $th) {
            return response()->error($th->getMessage(), $th->getCode() ?: 500);
        }
    }
}
