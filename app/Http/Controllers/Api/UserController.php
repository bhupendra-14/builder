<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends BaseController
{
    public function index()
    {
        $this->authorize('manage_users');

        $users = User::with('roles')->orderBy('created_at', 'desc')->paginate(20);
        return $this->paginatedResponse($users, 'Users retrieved');
    }

    public function store(Request $request)
    {
        $this->authorize('manage_users');


        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|string|exists:roles,name',
            'active' => 'boolean'
        ]);

        $data['password'] = Hash::make($data['password']);
        $user = User::create($data);
        $user->assignRole($request->role);

        return $this->successResponse($user, 'User created.', 201);
    }
    
    public function update(Request $request, int $id)
    {
        $this->authorize('manage_users');

        $user = User::findOrFail($id);
        
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,'.$id,
            'password' => 'nullable|string|min:8',
            'role' => 'sometimes|string|exists:roles,name',
            'active' => 'sometimes|boolean'
        ]);

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);
        
        if ($request->has('role')) {
            $user->syncRoles([$request->role]);
        }

        return $this->successResponse($user, 'User updated.');
    }

    public function destroy(int $id)
    {
        $this->authorize('manage_users');

        if (request()->user()->id === $id) {
            return $this->errorResponse('Cannot delete your own account.', 400);
        }
        
        User::destroy($id);
        
        return $this->successResponse(null, 'User deleted.');
    }
}
