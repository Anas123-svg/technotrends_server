<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProjectAssignedToUsers;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ProjectAssignedToUserController extends Controller
{
    public function updateProjectAssignedToUser(Request $request, $project_id)
    {
        $validatedData = $request->validate([
            'worker_ids' => 'required|array', 
            'worker_ids.*' => 'integer|exists:user,id', 
        ]);

        try {
            $projectAssignments = ProjectAssignedToUsers::where('project_id', $project_id)->get();

            if ($projectAssignments->isEmpty()) {
                return response()->json(['message' => 'No users assigned to this project'], 404);
            }

            $newAssignedUserIds = $validatedData['worker_ids'];

            $existingAssignedUserIds = $projectAssignments->pluck('user_id')->toArray();

            $usersToDelete = array_diff($existingAssignedUserIds, $newAssignedUserIds);

            ProjectAssignedToUsers::where('project_id', $project_id)
                ->whereIn('user_id', $usersToDelete)
                ->delete();

            foreach ($newAssignedUserIds as $userId) {
                if (!in_array($userId, $existingAssignedUserIds)) {
                    ProjectAssignedToUsers::create([
                        'project_id' => $project_id,
                        'user_id' => $userId,
                        'statusByUser' => $request->statusByUser ?? null,
                        'reason' => $request->reason ?? null,
                    ]);
                }
            }

            $updatedAssignments = ProjectAssignedToUsers::where('project_id', $project_id)->get();

            return response()->json([
                'message' => 'Project assigned users updated successfully',
                'data' => $updatedAssignments
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while updating project assignments.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
