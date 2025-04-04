<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ComplaintAssignedToUsers;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ComplaintAssignedToUserController extends Controller
{
    public function updateComplaintAssignedToUser(Request $request, $complaint_id)
    {
        
        $validatedData = $request->validate([
            'worker_ids' => 'required|array', 
            'worker_ids.*' => 'integer|exists:user,id',  
        ]);

        try {
            $complaintAssignments = ComplaintAssignedToUsers::where('complaint_id', $complaint_id)->get();

            if ($complaintAssignments->isEmpty()) {
                return response()->json(['message' => 'No users assigned to this complaint'], 404);
            }

            $newAssignedUserIds = $validatedData['worker_ids'];

            $existingAssignedUserIds = $complaintAssignments->pluck('user_id')->toArray();

            $usersToDelete = array_diff($existingAssignedUserIds, $newAssignedUserIds);

            ComplaintAssignedToUsers::where('complaint_id', $complaint_id)
                ->whereIn('user_id', $usersToDelete)
                ->delete();

            foreach ($newAssignedUserIds as $userId) {
                if (!in_array($userId, $existingAssignedUserIds)) {
                    ComplaintAssignedToUsers::create([
                        'complaint_id' => $complaint_id,
                        'user_id' => $userId,
                        'statusByUser' => $request->statusByUser ?? null,
                        'reason' => $request->reason ?? null,
                    ]);
                }
            }

            $updatedAssignments = ComplaintAssignedToUsers::where('complaint_id', $complaint_id)->get();

            return response()->json([
                'message' => 'Complaint assigned users updated successfully',
                'data' => $updatedAssignments
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while updating complaint assignments.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

