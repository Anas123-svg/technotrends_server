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
            'worker_ids' => 'nullable|array',
            'worker_ids.*' => 'nullable|integer|exists:user,id',
        ]);

        try {
            $projectAssignments = ProjectAssignedToUsers::where('project_id', $project_id)->get();

            if ($projectAssignments->isEmpty() && empty($validatedData['worker_ids'])) {
                return response()->json(['message' => 'No users assigned to this project and no new users provided'], 404);
            }

            $newAssignedUserIds = $validatedData['worker_ids'] ?? [];

            if (empty($newAssignedUserIds)) {
                ProjectAssignedToUsers::where('project_id', $project_id)->delete();
                return response()->json([
                    'message' => 'All users removed from the project',
                    'data' => [],
                ], 200);
            }

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




    public function updateAssignedUser(Request $request, $id)
    {
        $validatedData = $request->validate([
            'reason' => 'nullable|string|max:255',
            'statusByUser' => 'nullable|string|max:255', 
        ]);

        $projectAssignedToUser = ProjectAssignedToUsers::findOrFail($id);

        $projectAssignedToUser->update([
            'reason' => $validatedData['reason'] ?? $projectAssignedToUser->reason, 
            'statusByUser' => $validatedData['statusByUser'] ?? $projectAssignedToUser->statusByUser,
        ]);

        return response()->json([
            'message' => 'project assigned to user updated successfully',
            'data' => $projectAssignedToUser,
        ], 200);
    }
}
