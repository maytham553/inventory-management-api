<?php

namespace App\Http\Repositories;

use App\Models\User;
use Illuminate\Http\Request;

class UserRepository
{
    private User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }
    public function index(?string $search = null)
    {
        $query = $this->user::query()->orderBy('id', 'desc');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('type', 'like', "%{$search}%");
            });
        }

        return $query->paginate(15);
    }
    public function find($id)
    {
        return $this->user::findOrFail($id);
    }
    public function store(array $data)
    {
        return $this->user::create($data);
    }
    public function update( User $user , array $data)
    {
        $user->update($data);

        return $user->fresh();
    }
    public function destroy(User $user)
    {
        // Soft delete keeps the row (and everything referencing it), but the
        // access tokens are dead weight once the account is gone.
        $user->tokens()->delete();

        return $user->delete();
    }
}
