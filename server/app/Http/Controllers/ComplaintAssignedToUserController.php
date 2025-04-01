<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\complaintAssignedToUsers;

class ComplaintAssignedToUserController extends Controller
{
    public function updateComplaintAssignedToUser(Request $request, $id)
    {
        $complaint = complaintAssignedToUsers::find($id);

        if (!$complaint) {
            return response()->json(['message' => 'Complaint not found'], 404);
        }

        $validatedData = $request->validate([
            'statusByUser' => 'nullable|string|max:255',  
            'reason' => 'nullable|string|max:255',      
        ]);

        $complaint->update($validatedData);

        return response()->json(['message' => 'Complaint assigned user updated successfully', 'data' => $complaint], 200);
    }

}
