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
    public function index()
    {
        return $this->user::orderBy('id', 'desc')->paginate(15);
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
        return $user->update($data); 
    }
    public function destroy(User $user)
    {
        return $user->delete();
    }
}
