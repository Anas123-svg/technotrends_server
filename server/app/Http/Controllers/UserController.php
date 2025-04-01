<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    // Get all users
    public function index()
    {
        $users = User::with('head')->get();
        return response()->json($users);
    }

    // Create a new user
    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|unique:user',
                'password' => 'required|string|min:6',
                'head_id' => 'required|exists:head,id',
                'phone' => 'required|string|max:15',
                'status' => 'required|string',
            ]);
    
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'head_id' => $request->head_id,
                'phone' => $request->phone,
                'status' => $request->status,
            ]);
    
            return response()->json($user, 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    public function show($id)
    {
        $user = User::with('head')->findOrFail($id);
        return response()->json($user);
    }

    // Update a user
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'string|max:255',
            'email' => 'string|email|unique:user,email,' . $id,
            'password' => 'string|min:6',
            'head_id' => 'exists:head,id',
            'phone' => 'string|max:15',
            'status' => 'string',
        ]);

        $user->update([
            'name' => $request->name ?? $user->name,
            'email' => $request->email ?? $user->email,
            'password' => $request->password ? Hash::make($request->password) : $user->password,
            'head_id' => $request->head_id ?? $user->head_id,
            'phone' => $request->phone ?? $user->phone,
            'status' => $request->status ?? $user->status,
        ]);

        return response()->json($user);
    }

    // Delete a user
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json(['message' => 'User deleted successfully.']);
    }
}
