<?php

namespace App\Http\Controllers;

use App\Http\Repositories\UserRepository;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }
    public function index()
    {
        try {
            $users = $this->userRepository->index();
            return response()->success($users, 'Users retrieved successfully', 200);
        } catch (\Throwable $th) {
            return response()->error($th->getMessage(), $th->getCode() ?: 500);
        }
    }

    public function show($id)
    {
        try {
            $user = $this->userRepository->find($id);
            return response()->success($user, 'User retrieved successfully', 200);
        } catch (\Throwable $th) {
            return response()->error($th->getMessage(), $th->getCode() ?: 500);
        }
    }

    public function showUser()
    {
        try {
            $user = auth()->user();
            return response()->success($user, 'User retrieved successfully', 200);
        } catch (\Throwable $th) {
            return response()->error($th->getMessage(), $th->getCode() ?: 500);
        }
    }

    public function getCurrentUser()
    {
        try {
            $user = auth()->user();
            return response()->success($user, 'User retrieved successfully', 200);
        } catch (\Throwable $th) {
            return response()->error($th->getMessage(), $th->getCode() ?: 500);
        }
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'email' => 'required|unique:users|email',
            'password' => 'required|string|min:8',
            'type' => 'required|in:Admin,User,SuperAdmin',
        ]);
        try {
            $data['password'] = bcrypt($data['password']);
            $user = $this->userRepository->store($data);
            return response()->success($user, 'User created successfully', 201);
        } catch (\Throwable $th) {
            return response()->error($th->getMessage(), $th->getCode() ?: 500);
        }
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'name' => 'string',
            'email' => 'email|unique:users,email,' . $id,
            'password' => 'string|min:8',
            'type' => 'in:Admin,User,SuperAdmin',
        ]);
        try {
            $user = $this->userRepository->find($id);
            if (isset($data['password'])) {
                $data['password'] = bcrypt($data['password']);
            }
            $updatedUser = $this->userRepository->update($user, $data);
            return response()->success($updatedUser, 'User updated successfully', 200);
        } catch (\Throwable $th) {
            return response()->error($th->getMessage(), $th->getCode() ?: 500);
        }
    }

    public function updateCurrentUser(Request $request)
    {
        $data = $request->validate([
            'name' => 'string',
            'email' => 'email|unique:users,email,' . auth()->user()->id,
            'password' => 'string|min:8',
            'type' => 'in:Admin,User,SuperAdmin',
        ]);
        try {
            $userId = auth()->user()->id;
            $user = $this->userRepository->find($userId);
            if (isset($data['password'])) {
                $data['password'] = bcrypt($data['password']);
            }
            $updatedUser = $this->userRepository->update($user, $data);
            return response()->success($updatedUser, 'User updated successfully', 200);
        } catch (\Throwable $th) {
            return response()->error($th->getMessage(), $th->getCode() ?: 500);
        }
    }

    public function destroy($id)
    {
        try {
            $user = $this->userRepository->find($id);
            $user = $this->userRepository->destroy($user);
            return response()->success($user, 'User deleted successfully', 200);
        } catch (\Throwable $th) {
            return response()->error($th->getMessage(), $th->getCode() ?: 500);
        }
    }
}
