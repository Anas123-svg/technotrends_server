<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Models\Complaint;
use App\Models\ComplaintsJcReference;
use App\Models\ComplaintsDcReference;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;


class ComplaintController extends Controller
{
    public function index()
    {
        $complaints = Complaint::with(['complaintAssignedUsers', 'jcReferences', 'dcReferences'])
            ->orderBy('created_at', 'desc')
            ->get();

        $complaints->each(function ($complaint) {
            $complaint->users = collect($complaint->complaintAssignedUsers)->map(function ($assignedUser) {
                $user = User::find($assignedUser->user_id);

                if ($user) {
                    $user->statusByUser = $assignedUser->statusByUser;
                    $user->reason = $assignedUser->reason;
                    $user->assignedUserId = $assignedUser->id;
                    $user->makeHidden(['pivot', 'remember_token']);
                }

                return $user;
            });

            $complaint->makeHidden(['complaintAssignedUsers']);
        });

        return response()->json(
            $complaints->map(function ($complaint) {
                return [
                    'id' => $complaint->id,
                    'complaintReference' => $complaint->complaintReference,
                    'complaintImage' => $complaint->complaintImage,
                    'clientName' => $complaint->clientName,
                    'clientPhone' => $complaint->clientPhone,
                    'title' => $complaint->title,
                    'description' => $complaint->description,
                    'createdBy' => $complaint->createdBy,
                    'dueDate' => $complaint->dueDate,
                    'jcImage' => $complaint->jcImage,
                    'photos' => $complaint->photos,
                    'priority' => $complaint->priority,
                    'remarks' => $complaint->remarks,
                    'status' => $complaint->status,
                    'poNumber' => $complaint->poNumber,
                    'poDate' => $complaint->poDate,
                    'visitDates' => $complaint->visitDates,
                    'quotation' => $complaint->quotation,
                    'quotationDate' => $complaint->quotationDate,
                    'remarksDate' => $complaint->remarksDate,
                    'users' => $complaint->users,
                    'jcReferences' => $complaint->jcReferences,
                    'dcReferences' => $complaint->dcReferences,
                    'created_at'=> $complaint->created_at,
                    'updated_at'=> $complaint->updated_at        
                ];
            }),
            200
        );
    }


    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'complaintReference' => 'nullable|string|max:255',
                'complaintImage' => 'nullable|string|max:255',
                'clientName' => 'nullable|string|max:255',
                'clientPhone' => 'nullable|string|max:15',
                'title' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'dueDate' => 'nullable|string',
                'jcReference' => 'nullable|array',
                'jcReference.*.jcReference' => 'nullable|string|max:255',
                'jcReference.*.jcDate' => 'nullable|string',
                'jcReference.*.isJcDateEdited' => 'nullable|boolean',
                'dcReference' => 'nullable|array',
                'dcReference.*.dcReference' => 'nullable|string|max:255',
                'dcReference.*.dcDate' => 'nullable|string',
                'dcReference.*.isDcDateEdited' => 'nullable|boolean',
                'jcImage' => 'nullable|string|max:255',
                'photos' => 'nullable|array',
                'priority' => 'nullable|in:Low,Medium,High',
                'remarks' => 'nullable|string',
                'status' => 'nullable|string',
                'poNumber' => 'nullable|string|max:255',
                'poDate' => 'nullable|string',
                'visitDates' => 'nullable|array',
                'quotation' => 'nullable|string',
                'quotationDate' => 'nullable|string',
                'remarksDate' => 'nullable|string',
                'createdBy' => 'nullable|string',
            ]);

            // Handle PO-related logic
            if ($request->has('poNumber') && !empty($request->input('poNumber'))) {
                $validated['poDate'] = $request->input('poDate') ?: now();
            }

        if ($request->has('jcReference') || $request->has('dcReference')) {
            $validated['status'] = 'Completed';
        }
            // Handle Remarks-related logic
            if ($request->has('remarks') && !empty($request->input('remarks'))) {
                $validated['remarksDate'] = $request->input('remarksDate') ?: now();
            }

            // Handle Quotation-related logic
            if ($request->has('quotation') && !empty($request->input('quotation'))) {
                $validated['quotationDate'] = $request->input('quotationDate') ?: now();
            }

            // Create the complaint
            $complaint = Complaint::create($validated);

            // Handle the jcReference logic (store multiple jcReference entries)
            if ($request->has('jcReference') && !empty($request->jcReference)) {
                foreach ($request->jcReference as $jcData) {
                    $jcDate = $jcData['jcDate'] ?? now();
                    $jcReference = new ComplaintsJcReference([
                        'complaint_id' => $complaint->id,
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
                    $dcReference = new ComplaintsDcReference([
                        'complaint_id' => $complaint->id,
                        'dcReference' => $dcData['dcReference'],
                        'dcDate' => $dcDate,
                        'isDcDateEdited' => $dcData['isDcDateEdited'] ?? false,
                    ]);
                    $dcReference->save();
                }
            }

            if ($request->has('assignedWorkers')) {
                $complaint->users()->sync($request->assignedWorkers);
            }

            return response()->json([
                'id' => $complaint->id,
                'complaintReference' => $complaint->complaintReference,
                'complaintImage' => $complaint->complaintImage,
                'clientName' => $complaint->clientName,
                'clientPhone' => $complaint->clientPhone,
                'title' => $complaint->title,
                'description' => $complaint->description,
                'createdBy' => $complaint->createdBy,
                'dueDate' => $complaint->dueDate,
                'jcImage' => $complaint->jcImage,
                'photos' => $complaint->photos,
                'priority' => $complaint->priority,
                'remarks' => $complaint->remarks,
                'status' => $complaint->status,
                'poNumber' => $complaint->poNumber,
                'poDate' => $complaint->poDate,
                'visitDates' => $complaint->visitDates,
                'quotation' => $complaint->quotation,
                'quotationDate' => $complaint->quotationDate,
                'remarksDate' => $complaint->remarksDate,
                'users' => $complaint->users,
                'jcReferences' => $complaint->jcReferences,
                'dcReferences' => $complaint->dcReferences,
                'created_at'=> $complaint->created_at,
                'updated_at'=> $complaint->updated_at
    
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'message' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred',
                'message' => $e->getMessage(),
            ], 500);
        }
    }



    public function show($complaintId)
    {
        $complaint = Complaint::with(['complaintAssignedUsers', 'jcReferences', 'dcReferences'])
            ->findOrFail($complaintId);

        $complaint->users = collect($complaint->complaintAssignedUsers)->map(function ($assignedUser) {
            $user = User::find($assignedUser->user_id);

            if ($user) {
                $user->statusByUser = $assignedUser->statusByUser;
                $user->reason = $assignedUser->reason;
                $user->assignedUserId = $assignedUser->id;
                $user->makeHidden(['pivot', 'remember_token']);
            }

            return $user;
        });

        $complaint->makeHidden(['complaintAssignedUsers']);

        return response()->json([
            'id' => $complaint->id,
            'complaintReference' => $complaint->complaintReference,
            'complaintImage' => $complaint->complaintImage,
            'clientName' => $complaint->clientName,
            'clientPhone' => $complaint->clientPhone,
            'title' => $complaint->title,
            'description' => $complaint->description,
            'createdBy' => $complaint->createdBy,
            'dueDate' => $complaint->dueDate,
            'jcImage' => $complaint->jcImage,
            'photos' => $complaint->photos,
            'priority' => $complaint->priority,
            'remarks' => $complaint->remarks,
            'status' => $complaint->status,
            'poNumber' => $complaint->poNumber,
            'poDate' => $complaint->poDate,
            'visitDates' => $complaint->visitDates,
            'quotation' => $complaint->quotation,
            'quotationDate' => $complaint->quotationDate,
            'remarksDate' => $complaint->remarksDate,
            'users' => $complaint->users,
            'jcReferences' => $complaint->jcReferences,
            'dcReferences' => $complaint->dcReferences,
            'created_at'=> $complaint->created_at,
            'updated_at'=> $complaint->updated_at
        ], 200);
    }
    public function update(Request $request, $id)
    {
        $complaint = Complaint::findOrFail($id);

        $validated = $request->validate([
            'complaintReference' => 'nullable|string|max:255',
            'complaintImage' => 'nullable|string|max:255',
            'clientName' => 'nullable|string|max:255',
            'clientPhone' => 'nullable|string|max:15',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'createdBy' => 'nullable|string',
            'dueDate' => 'nullable|string',
            'jcImage' => 'nullable|string|max:255',
            'photos' => 'nullable|array',
            'priority' => 'nullable|in:Low,Medium,High',
            'remarks' => 'nullable|string',
            'status' => 'nullable|in:Pending,In Progress,Resolved,Closed',
            'poNumber' => 'nullable|string|max:255',
            'poDate' => 'nullable|string',
            'visitDates' => 'nullable|array',
            'quotation' => 'nullable|string',
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

        // Handle PO-related logic 
        if ($request->has('poNumber') && !empty($request->input('poNumber'))) {
            if (empty($complaint->poDate) && !$request->has('poDate')) {
                $validated['poDate'] = now();
            }
        }
        if ($request->has('jcReference') || $request->has('dcReference')) {
            $validated['status'] = 'Completed';
        }
        if ($request->has('poDate')) {
            $validated['poDate'] = $request->input('poDate');
            $validated['isPoDateEdited'] = true;
        }

        // Handle Remarks-related logic
        if ($request->has('remarks') && !empty($request->input('remarks'))) {
            if (empty($complaint->remarksDate) && !$request->has('remarksDate')) {
                $validated['remarksDate'] = now();
            }
        }

        if ($request->has('remarksDate')) {
            $validated['remarksDate'] = $request->input('remarksDate');
            $validated['isRemarksDateEdited'] = true;
        }

        // Handle Quotation-related logic 
        if ($request->has('quotation') && !empty($request->input('quotation'))) {
            if (empty($complaint->quotationDate) && !$request->has('quotationDate')) {
                $validated['quotationDate'] = now();
            }
        }

        if ($request->has('quotationDate')) {
            $validated['quotationDate'] = $request->input('quotationDate');
            $validated['isDueDateEdited'] = true;
        }

        // Handle DueDate-related logic 
        if ($request->has('dueDate')) {
            $validated['dueDate'] = $request->input('dueDate');
            $validated['isDueDateEdited'] = true;
        }

        // Filter out null or empty values
        $validated = array_filter($validated, function ($value) {
            return !is_null($value) && $value !== '';
        });

        $complaint->update($validated);

        if ($request->has('jcReference') && !empty($request->jcReference)) {
            $newJcReferences = collect($request->jcReference)->pluck('jcReference')->toArray();

            ComplaintsJcReference::where('complaint_id', $complaint->id)
                ->whereNotIn('jcReference', $newJcReferences)
                ->delete();

            foreach ($request->jcReference as $jcData) {
                $jcReference = ComplaintsJcReference::where('complaint_id', $complaint->id)
                    ->where('jcReference', $jcData['jcReference'])
                    ->first();

                if ($jcReference) {
                    $jcReference->jcDate = $jcData['jcDate'] ?? now();
                    $jcReference->isJcDateEdited = true;
                    $jcReference->save();
                } else {
                    ComplaintsJcReference::create([
                        'complaint_id' => $complaint->id,
                        'jcReference' => $jcData['jcReference'],
                        'jcDate' => $jcData['jcDate'] ?? now(),
                        'isJcDateEdited' => true,
                    ]);
                }
            }
        }

        if ($request->has('dcReference') && !empty($request->dcReference)) {
            $newDcReferences = collect($request->dcReference)->pluck('dcReference')->toArray();

            ComplaintsDcReference::where('complaint_id', $complaint->id)
                ->whereNotIn('dcReference', $newDcReferences)
                ->delete();

            foreach ($request->dcReference as $dcData) {
                $dcReference = ComplaintsDcReference::where('complaint_id', $complaint->id)
                    ->where('dcReference', $dcData['dcReference'])
                    ->first();

                if ($dcReference) {
                    $dcReference->dcDate = $dcData['dcDate'] ?? now();
                    $dcReference->isDcDateEdited = true;
                    $dcReference->save();
                } else {
                    ComplaintsDcReference::create([
                        'complaint_id' => $complaint->id,
                        'dcReference' => $dcData['dcReference'],
                        'dcDate' => $dcData['dcDate'] ?? now(),
                        'isDcDateEdited' => true,
                    ]);
                }
            }
        }

        if ($request->has('assignedWorkers')) {
            $complaint->users()->sync($request->assignedWorkers);
        }

        return response()->json($complaint->load(['users', 'jcReferences', 'dcReferences']));
    }



    public function destroy($id)
    {
        $complaint = Complaint::findOrFail($id);
        $complaint->delete();
        return response()->json(['message' => 'Complaint deleted successfully.']);
    }

    /*public function assignToHead(Request $request, $complaintId)
    {
        $validated = $request->validate([
            'head_id' => 'required|exists:head,id',
        ]);
    
        $complaint = Complaint::findOrFail($complaintId);
        $complaint->update(['assignedHead' => $validated['head_id']]);
    
        return response()->json([
            'message' => 'Complain assigned to head successfully',
            'Complain' => $complaint->load('head','users'),
        ]);
    }*/

    public function assignToWorkers(Request $request, $complaintId)
    {
        $validated = $request->validate([
            'worker_ids' => 'required|array',
            'worker_ids.*' => 'exists:user,id',
        ]);

        $complaint = Complaint::findOrFail($complaintId);
        $complaint->users()->sync($validated['worker_ids']);
        $complaint->status = 'Pending';
        $complaint->save();

        return response()->json([
            'message' => 'Complain assigned to workers successfully',
            'Complain' => $complaint->load('users'),
        ]);
    }

}

