<?php

namespace App\Http\Controllers;

use App\Models\Head;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class HeadController extends Controller
{
    public function index()
    {
        $heads = Head::all();
        return response()->json($heads);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:head',
            'password' => 'required|string|min:6',
            'phone' => 'required|string|max:15',
            'department' => 'required|string',
            'status' => 'required|string',
        ]);

        $head = Head::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'department' => $request->department,
            'status' => $request->status,
        ]);

        return response()->json($head, 201);
    }
    public function show($id)
    {
        $head = Head::findOrFail($id);
        return response()->json($head);
    }

    public function update(Request $request, $id)
    {
        $head = Head::findOrFail($id);

        $request->validate([
            'name' => 'string|max:255',
            'email' => 'string|email|unique:head,email,' . $id,
            'password' => 'string|min:6',
            'phone' => 'string|max:15',
            'department' => 'string',
            'status' => 'string',
        ]);

        $head->update([
            'name' => $request->name ?? $head->name,
            'email' => $request->email ?? $head->email,
            'password' => $request->password ? Hash::make($request->password) : $head->password,
            'phone' => $request->phone ?? $head->phone,
            'department' => $request->department ?? $head->department,
            'status' => $request->status ?? $head->status,
        ]);

        return response()->json($head);
    }

    public function destroy($id)
    {
        $head = Head::findOrFail($id);
        $head->delete();

        return response()->json(['message' => 'Head deleted successfully.']);
    }
}
