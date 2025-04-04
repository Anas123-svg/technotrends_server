<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ComplaintAssignedToUsers;
use Illuminate\Validation\ValidationException;
use Exception;

class ComplaintAssignedToUserController extends Controller
{
    public function updateComplaintAssignedToUser(Request $request, $complaint_id)
    {
        try {
            $validatedData = $request->validate([
                'worker_ids' => 'nullable|array',
                'worker_ids.*' => 'nullable|integer|exists:user,id',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }

        try {
            $complaintAssignments = ComplaintAssignedToUsers::where('complaint_id', $complaint_id)->get();

            if ($complaintAssignments->isEmpty() && empty($validatedData['worker_ids'])) {
                return response()->json(['message' => 'No users assigned to this complaint and no new users provided'], 404);
            }

            $newAssignedUserIds = $validatedData['worker_ids'] ?? [];

            if (empty($newAssignedUserIds)) {
                ComplaintAssignedToUsers::where('complaint_id', $complaint_id)->delete();
                return response()->json([
                    'message' => 'All users removed from the complaint successfully',
                    'data' => [],
                ], 200);
            }

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
                'data' => $updatedAssignments,
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'An error occurred while updating complaint assignments.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }




    public function updateAssignedUser(Request $request, $id)
    {
        $validatedData = $request->validate([
            'reason' => 'nullable|string|max:255',
            'statusByUser' => 'nullable|string|max:255', 
        ]);

        $complaintAssignedToUser = ComplaintAssignedToUsers::findOrFail($id);

        $complaintAssignedToUser->update([
            'reason' => $validatedData['reason'] ?? $complaintAssignedToUser->reason, 
            'statusByUser' => $validatedData['statusByUser'] ?? $complaintAssignedToUser->statusByUser,
        ]);

        return response()->json([
            'message' => 'Complaint assigned to user updated successfully',
            'data' => $complaintAssignedToUser,
        ], 200);
    }



    public function getComplaintsAssignedToUser($user_id)
    {
        try {
            $assignedComplaints = ComplaintAssignedToUsers::where('user_id', $user_id)->get();
        
            if ($assignedComplaints->isEmpty()) {
                return response()->json([
                    'message' => 'No complaints assigned to this user.',
                    'data' => [
                        'response' => [],
                    ]
                ], 404);
            }
        
            $complaints = $assignedComplaints->map(function ($assignment) {
                return $assignment->complaint; 
            });
        
            return response()->json($complaints, 200);
        
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while retrieving the complaints assigned to the user.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
}
