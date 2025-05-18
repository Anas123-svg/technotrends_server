<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use App\Models\ProjectsDcReference;
use App\Models\ProjectsJcReference;
use Illuminate\Http\Request;
use App\Models\Invoice;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = Project::with(['users', 'projectAssignedUsers', 'jcReferences', 'dcReferences'])->orderBy('created_at', 'desc')->get();

        $projects->each(function ($project) {
            $project->users = $project->users->map(function ($user) use ($project) {
                $assignedUser = $project->projectAssignedUsers->firstWhere('user_id', $user->id);

                if ($assignedUser) {
                    $user->statusByUser = $assignedUser->statusByUser;
                    $user->reason = $assignedUser->reason;
                    $user->assignedUserId = $assignedUser->id;
                }

                $user->makeHidden(['pivot', 'remember_token']);

                return $user;
            });

            $project->makeHidden(['projectAssignedUsers']);
        });

        return response()->json($projects);
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'description' => 'nullable|string',
            'poNumber' => 'nullable|string',
            'poImage' => 'nullable|string',
            'clientName' => 'nullable|string',
            'clientPhone' => 'nullable|string',
            'surveyPhotos' => 'nullable|array',
            'quotationReference' => 'nullable|string',
            'quotationImage' => 'nullable|string',
            'jcImage' => 'nullable|string',
            'dcImage' => 'nullable|string',
            'status' => 'nullable|string|in:Pending,In Progress,On Hold,Completed,Cancelled',
            'remarks' => 'nullable|string',
            'createdBy' => 'nullable|string',
            'dueDate' => 'nullable|date',
            'poDate' => 'nullable|string',
            'surveyDate' => 'nullable|string',
            'quotationDate' => 'nullable|string',
            'remarksDate' => 'nullable|string',
            'jcReference' => 'nullable|array',
            'jcReference.*.jcReference' => 'nullable|string|max:255',
            'jcReference.*.jcDate' => 'nullable|string',
            'jcReference.*.isJcDateEdited' => 'nullable|boolean',
            'dcReference' => 'nullable|array',
            'dcReference.*.dcReference' => 'nullable|string|max:255',
            'dcReference.*.dcDate' => 'nullable|string',
            'dcReference.*.isDcDateEdited' => 'nullable|boolean',

        ]);
            $hasValidJcReference = $request->has('jcReference') && is_array($request->jcReference) && count($request->jcReference) > 0;
            $hasValidDcReference = $request->has('dcReference') && is_array($request->dcReference) && count($request->dcReference) > 0;

            if ($hasValidJcReference || $hasValidDcReference) {
                $validated['status'] = 'Completed';
            }

        if ($request->has('poNumber') && !empty($request->input('poNumber'))) {
            $validated['poDate'] = $request->input('poDate') ?: now();
        }
        if ($request->has('remarks') && !empty($request->input('remarks'))) {
            $validated['remarksDate'] = $request->input('remarksDate') ?: now();
        }

        if ($request->has('surveyPhotos') && !empty($request->input('surveyPhotos'))) {
            $validated['surveyDate'] = $request->input('surveyDate') ?: now();
        }

        if ($request->has('quotationReference') && !empty($request->input('quotationReference'))) {
            $validated['quotationDate'] = $request->input('quotationDate') ?: now();
        }

        $project = Project::create($validated);

        if ($request->has('jcReference') && !empty($request->jcReference)) {
            foreach ($request->jcReference as $jcData) {
                $jcDate = $jcData['jcDate'] ?? now();
                $jcReference = new ProjectsJcReference([
                    'project_id' => $project->id,
                    'jcReference' => $jcData['jcReference'],
                    'jcDate' => $jcDate,
                    'isJcDateEdited' => $jcData['isJcDateEdited'] ?? false,
                ]);
                $jcReference->save();
            }
        }


        if ($request->has('dcReference') && !empty($request->dcReference)) {
            foreach ($request->dcReference as $dcData) {
                $dcDate = $dcData['dcDate'] ?? now();
                $dcReference = new ProjectsDcReference([
                    'project_id' => $project->id,
                    'dcReference' => $dcData['dcReference'],
                    'dcDate' => $dcDate,
                    'isDcDateEdited' => $dcData['isDcDateEdited'] ?? false,
                ]);
                $dcReference->save();
            }
        }

        if ($request->has('assignedWorkers')) {
            $project->users()->attach($request->assignedWorkers);
        }

        $project = $project->load(['users']);

        $project->users->each(function ($user) {
            $user->makeHidden(['pivot', 'remember_token']);
        });

        return response()->json($project, 201);
    }

    public function show($id)
    {
        $project = Project::with(['users', 'projectAssignedUsers', 'jcReferences', 'dcReferences'])->findOrFail($id);

        $project->users = $project->users->map(function ($user) use ($project) {
            $assignedUser = $project->projectAssignedUsers->firstWhere('user_id', $user->id);

            if ($assignedUser) {
                $user->statusByUser = $assignedUser->statusByUser;
                $user->reason = $assignedUser->reason;
                $user->assignedUserId = $assignedUser->id;
            }

            $user->makeHidden(['pivot', 'remember_token']);

            return $user;
        });

        $project->makeHidden(['projectAssignedUsers']);

        return response()->json($project);
    }



    public function update(Request $request, $id)
    {
        try {
            $project = Project::findOrFail($id);

            $validated = $request->validate([
                'description' => 'nullable|string',
                'poNumber' => 'nullable|string',
                'poImage' => 'nullable|string',
                'clientName' => 'nullable|string',
                'clientPhone' => 'nullable|string',
                'surveyPhotos' => 'nullable|array',
                'quotationReference' => 'nullable|string',
                'quotationImage' => 'nullable|string',
                'jcImage' => 'nullable|string',
                'dcImage' => 'nullable|string',
                'status' => 'nullable|string|in:Pending,In Progress,On Hold,Completed,Cancelled',
                'remarks' => 'nullable|string',
                'dueDate' => 'nullable|string',
                'poDate' => 'nullable|string',
                'surveyDate' => 'nullable|string',
                'createdBy' => 'nullable|string',
                'quotationDate' => 'nullable|string',
                'remarksDate' => 'nullable|string',
                'jcReference' => 'nullable|array',
                'jcReference.*.jcReference' => 'nullable|string|max:255',
                'jcReference.*.jcDate' => 'nullable|string',
                'jcReference.*.isJcDateEdited' => 'nullable|boolean',
                'dcReference' => 'nullable|array',
                'dcReference.*.dcReference' => 'nullable|string|max:255',
                'dcReference.*.dcDate' => 'nullable|string',
                'dcReference.*.isDcDateEdited' => 'nullable|boolean',

            ]);
            $hasValidJcReference = $request->has('jcReference') && is_array($request->jcReference) && count($request->jcReference) > 0;
            $hasValidDcReference = $request->has('dcReference') && is_array($request->dcReference) && count($request->dcReference) > 0;

            if ($hasValidJcReference || $hasValidDcReference) {
                $validated['status'] = 'Completed';
            }


            // Automatically set poDate if poNumber is provided
            if ($request->has('poNumber') && !empty($request->input('poNumber'))) {
                if (empty($project->poDate) && !$request->has('poDate')) {
                    $validated['poDate'] = now();
                }
            }
            if ($request->has('poDate')) {
                $validated['poDate'] = $request->input('poDate');
                $validated['isPoDateEdited'] = true;
            }

            // Automatically set surveyDate if survey photos is provided
            if ($request->has('surveyPhotos') && !empty($request->input('surveyPhotos'))) {
                if (empty($project->surveyDate) && !$request->has('surveyDate')) {
                    $validated['surveyDate'] = now();
                }
            }
            if ($request->has('surveyDate')) {
                $validated['surveyDate'] = $request->input('surveyDate');
                $validated['isSurveyDateEdited'] = true;
            }

            // Automatically set remarksDate if remarks is provided
            if ($request->has('remarks') && !empty($request->input('remarks'))) {
                if (empty($project->remarksDate) && !$request->has('remarksDate')) {
                    $validated['remarksDate'] = now();
                }
            }
            if ($request->has('remarksDate')) {
                $validated['remarksDate'] = $request->input('remarksDate');
                $validated['isRemarksDateEdited'] = true;
            }

            // Automatically set quotationDate if quotationReference is provided
            if ($request->has('quotationReference') && !empty($request->input('quotationReference'))) {
                if (empty($project->quotationDate) && !$request->has('quotationDate')) {
                    $validated['quotationDate'] = now();
                }
            }
            if ($request->has('quotationDate')) {
                $validated['quotationDate'] = $request->input('quotationDate');
                $validated['isQuotationDateEdited'] = true;
            }

            if ($request->has('dueDate')) {
                $validated['dueDate'] = $request->input('dueDate');
                $validated['isDueDateEdited'] = true;
            }

            $validated = array_filter($validated, function ($value) {
                return !is_null($value) && $value !== '';
            });

            $project->update($validated);

            if ($request->has('jcReference') && !empty($request->jcReference)) {
                $newJcReferences = collect($request->jcReference)->pluck('jcReference')->toArray();

                ProjectsJcReference::where('project_id', $project->id)
                    ->whereNotIn('jcReference', $newJcReferences)
                    ->delete();

                foreach ($request->jcReference as $jcData) {
                    $jcReference = ProjectsJcReference::where('project_id', $project->id)
                        ->where('jcReference', $jcData['jcReference'])
                        ->first();

                    if ($jcReference) {
                        $jcReference->jcDate = $jcData['jcDate'] ?? now();
                        $jcReference->isJcDateEdited = true;
                        $jcReference->save();
                    } else {
                        ProjectsJcReference::create([
                            'project_id' => $project->id,
                            'jcReference' => $jcData['jcReference'],
                            'jcDate' => $jcData['jcDate'] ?? now(),
                            'isJcDateEdited' => true,
                        ]);
                    }
                }
            }

            if ($request->has('dcReference') && !empty($request->dcReference)) {
                $newDcReferences = collect($request->dcReference)->pluck('dcReference')->toArray();

                ProjectsDcReference::where('project_id', $project->id)
                    ->whereNotIn('dcReference', $newDcReferences)
                    ->delete();

                foreach ($request->dcReference as $dcData) {
                    $dcReference = ProjectsDcReference::where('project_id', $project->id)
                        ->where('dcReference', $dcData['dcReference'])
                        ->first();

                    if ($dcReference) {
                        $dcReference->dcDate = $dcData['dcDate'] ?? now();
                        $dcReference->isDcDateEdited = true;
                        $dcReference->save();
                    } else {
                        ProjectsDcReference::create([
                            'project_id' => $project->id,
                            'dcReference' => $dcData['dcReference'],
                            'dcDate' => $dcData['dcDate'] ?? now(),
                            'isDcDateEdited' => true,
                        ]);
                    }
                }
            }

            if ($request->has('assignedWorkers')) {
                $project->users()->sync($request->assignedWorkers);
            }

            $existingInvoice = Invoice::where('linkedProject', $project->id)->first();

            if (!$existingInvoice && ($request->has('jcReference') || $request->has('dcReference'))) {
                Invoice::create([
                    'linkedProject' => $project->id,
                    'clientName' => $project->clientName,
                    'poNumber' => $project->poNumber,
                    'poDate' => $project->poDate,
                    'invoiceDate' => now(),
                    'status' => 'Pending',
                ]);
            }

            return response()->json(
                $project->load(['users', 'jcReferences', 'dcReferences'])->makeHidden(['pivot', 'remember_token'])
            );

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Project not found.'], 404);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => 'Validation failed.', 'details' => $e->errors()], 422);

        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json(['error' => 'Database error occurred.', 'message' => $e->getMessage()], 500);

        } catch (\Exception $e) {
            return response()->json(['error' => 'An unexpected error occurred.', 'message' => $e->getMessage()], 500);
        }
    }



    public function destroy($id)
    {
        $project = Project::findOrFail($id);
        $project->delete();
        return response()->json(['message' => 'Project deleted successfully']);
    }

    /*    public function assignToHead(Request $request, $projectId)
    {
        $validated = $request->validate([
            'head_id' => 'required|exists:head,id',
        ]);

        $project = Project::findOrFail($projectId);
        $project->update(['assignedHead' => $validated['head_id']]);

        return response()->json([
            'message' => 'Project assigned to head successfully',
            'project' => $project->load('head','users')->makeHidden(['pivot', 'remember_token']),
        ]);
    }*/
    public function assignToWorkers(Request $request, $projectId)
    {
        $validated = $request->validate([
            'worker_ids' => 'required|array',
            'worker_ids.*' => 'exists:user,id',
        ]);

        $project = Project::findOrFail($projectId);
        $project->users()->sync($validated['worker_ids']);
        $project->status = 'Pending';
        $project->save();

        return response()->json([
            'message' => 'Project assigned to workers successfully',
            'project' => $project->load('users')->makeHidden(['pivot', 'remember_token']),
        ]);
    }


}
