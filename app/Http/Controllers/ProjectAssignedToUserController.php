<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Models\ProjectAssignedToUsers;
class ProjectAssignedToUserController extends Controller
{
    public function updateProjectAssignedToUser(Request $request, $id)
    {
        $project = ProjectAssignedToUsers::find($id);

        if (!$project) {
            return response()->json(['message' => 'project not found'], 404);
        }

        $validatedData = $request->validate([
            'statusByUser' => 'nullable|string|max:255',  
            'reason' => 'nullable|string|max:255',      
        ]);

        $project->update($validatedData);

        return response()->json(['message' => 'project assigned user updated successfully', 'data' => $project], 200);
    }
}
