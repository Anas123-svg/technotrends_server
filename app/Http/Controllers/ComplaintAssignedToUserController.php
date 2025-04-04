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
}
