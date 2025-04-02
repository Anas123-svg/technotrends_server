<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Head;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Role;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    /**
     * Login user and generate token
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
            'role' => 'required|in:admin,head,user,director', 
        ]);
    
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }
    
        $role = $request->role;
        $email = $request->email;
        $password = $request->password;
    
        if ($role == 'admin' || $role == 'director') {
            $user = Admin::with(['complaints', 'projects'])->where('email', $email)->first();

            if (!$user) {
                return response()->json(['error' => 'Invalid email or role'], 404);
            }
    
            if ($role == 'director' && !$user->isDirector) {
                return response()->json(['error' => 'Invalid role: User is not a director'], 403);
            }
            if ($user && $user->isDirector) {
                $role = 'director';
            }
        } elseif ($role == 'head') {
         //   $user = Head::with(['complaints', 'projects'])->where('email', $email)->first();
         $user = Head::where('email', $email)->first();
        } elseif ($role == 'user') {
            $user = User::with(['complaints', 'projects'])->where('email', $email)->first();
        } else {
            return response()->json(['error' => 'Invalid role'], 400);
        }
    
        if (!$user) {
            return response()->json(['error' => 'Invalid email or role'], 404);
        }
    
        if ($user->status != 'approved') {
            return response()->json(['error' => 'User is not approved yet'], 401);
        }
    
        if (!Hash::check($password, $user->password)) {
            return response()->json(['error' => 'Invalid password'], 401);
        }
    
        $token = $user->createToken('YourAppName')->plainTextToken;
        return response()->json([
            'token' => $token,
            'role' => $role, 
            'user' => $user,
        ]);
    }
    
    
    public function user(Request $request)
    {
        $user = $request->user();
    
        Log::info('Fetching user details', [
            'user_id' => $user->id,
            'role' => get_class($user),
        ]);
    
        if ($user instanceof \App\Models\Admin) {
            $user->load(['complaints', 'projects']);
            $role = $user->isDirector ? 'director' : 'admin';
            Log::info('Admin data loaded', ['user_id' => $user->id]);
        } elseif ($user instanceof \App\Models\Head) {
           // $user->load(['complaints', 'projects']);
            $role = 'head';
            Log::info('Head data loaded', ['user_id' => $user->id]);
        } elseif ($user instanceof \App\Models\User) {
            $user->load(['complaints', 'projects']);
            $role = 'user';
            Log::info('User data loaded', ['user_id' => $user->id]);
        } else {
            Log::warning('Unknown user type encountered', ['user_id' => $user->id]);
        }
    
        return response()->json([
            'role'=> $role,
            'user' => $user,
        ]);
    }


    public function register(Request $request)
    {
        // Validate the role field
        $validator = Validator::make($request->all(), [
            'role' => 'required|string|in:admin,head,user,director',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }
    
        $role = $request->input('role');
        $data = $request->all();
    
        switch ($role) {
            case 'admin':
                $validator = Validator::make($data, [
                    'name' => 'required|string|max:255',
                    'email' => 'required|email|unique:admin,email',
                    'password' => 'required|string|min:6',
                    'phone' => 'required|string|max:15',
                    'status' => 'nullable|string|max:50',
                ]);
                if ($validator->fails()) {
                    return response()->json(['error' => $validator->errors()], 422);
                }
    
                $admin = Admin::create([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'password' => Hash::make($data['password']),
                    'phone' => $data['phone'],
                    'status' => 'pending',
                    'isDirector' => false,
                ]);
                return response()->json(['message' => 'Admin created. Waiting for approval.'], 201);
    
            case 'director':
                $validator = Validator::make($data, [
                    'name' => 'required|string|max:255',
                    'email' => 'required|email|unique:admin,email',
                    'password' => 'required|string|min:6',
                    'phone' => 'required|string|max:15',
                    'status' => 'nullable|string|max:50',
                ]);
                if ($validator->fails()) {
                    return response()->json(['error' => $validator->errors()], 422);
                }
    
                $director = Admin::create([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'password' => Hash::make($data['password']),
                    'phone' => $data['phone'],
                    'status' => 'pending',
                    'isDirector' => true,
                ]);
                return response()->json(['message' => 'Director created. Waiting for approval.'], 201);
    
            case 'head':
                $validator = Validator::make($data, [
                    'name' => 'required|string|max:255',
                    'email' => 'required|string|email|unique:head,email',
                    'password' => 'required|string|min:6',
                    'phone' => 'required|string|max:15',
                    'department' => 'required|string',
                    'status' => 'nullable|string',
                ]);
                if ($validator->fails()) {
                    return response()->json(['error' => $validator->errors()], 422);
                }
    
                $head = Head::create([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'password' => Hash::make($data['password']),
                    'phone' => $data['phone'],
                    'department' => $data['department'],
                    'status' => 'pending',
                ]);
                return response()->json(['message' => 'Head created. Waiting for approval.'], 201);
    
            case 'user':
                $validator = Validator::make($data, [
                    'name' => 'required|string|max:255',
                    'email' => 'required|string|email|unique:user,email',
                    'password' => 'required|string|min:6',
                    'head_id' => 'required|exists:head,id',
                    'phone' => 'required|string|max:15',
                    'status' => 'nullable|string',
                ]);
                if ($validator->fails()) {
                    return response()->json(['error' => $validator->errors()], 422);
                }
    
                $user = User::create([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'password' => Hash::make($data['password']),
                    'head_id' => $data['head_id'],
                    'phone' => $data['phone'],
                    'status' => 'pending',
                ]);
                return response()->json(['message' => 'User created. Waiting for approval.'], 201);
    
            default:
                return response()->json(['error' => 'Invalid role provided.'], 400);
        }
    }
    
    public function logout(Request $request)
    {
        $user = $request->user();
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'User logged out successfully.'], 200);
    }

    
}
