<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use App\Models\Project;

use Exception;
class InvoiceController extends Controller
{
    public function index()
    {
        $invoices = Invoice::with(['project', 'project.jcReferences', 'project.dcReferences'])
            ->orderBy('created_at', 'desc')
            ->get();
    
        $invoices->map(function ($invoice) {
            $invoice->jcReferences = $invoice->project->jcReferences;
            $invoice->dcReferences = $invoice->project->dcReferences;
            $invoice->project = $invoice->project;
            //unset($invoice->project);
            return $invoice;
        });
    
        return response()->json($invoices);
    }
    

public function store(Request $request)
{
    try {
        // Validate the request data
        $validated = $request->validate([
            'invoiceReference' => 'nullable|string|max:255',
            'invoiceImage' => 'nullable|string|max:255',
            'amount' => 'nullable|numeric',
            'paymentTerms' => 'nullable|string',
            'creditDays' => 'nullable|string|max:255',
            'dueDate' => 'nullable|string',
            'linkedProject' => 'nullable|exists:projects,id', 
            'status' => 'nullable|string',
            'clientName' => 'nullable|string|max:255',
            'poNumber' => 'nullable|string|max:255',
            'poDate' => 'nullable|string',
            'jcReference' => 'nullable|array',
            'jcDate' => 'nullable|string',
            'dcReference' => 'nullable|array',
            'dcDate' => 'nullable|string',
            'invoiceDate' => 'nullable|string',
        ]);

        if ($request->has('linkedProject')) {
            $project = Project::find($request->input('linkedProject'));
            
            if ($project) {
                $validated['clientName'] = $project->clientName;
                $validated['poNumber'] = $project->poNumber;
                $validated['poDate'] = $project->poDate;
                $validated['jcReference'] = $project->jcReference;
                $validated['jcDate'] = $project->jcDate;
                $validated['dcReference'] = $project->dcReference;
                $validated['dcDate'] = $project->dcDate;
            } else {
                return response()->json([
                    'error' => 'Linked project not found',
                    'message' => 'The specified linked project does not exist.'
                ], 404);
            }
        }

        if ($request->has('poNumber') && empty($validated['poDate'])) {
            $validated['poDate'] = $request->input('poDate') ?: now();  
        }

        if ($request->has('jcReference') && empty($validated['jcDate'])) {
            $validated['jcDate'] = $request->input('jcDate') ?: now();  
        }

        if ($request->has('dcReference') && empty($validated['dcDate'])) {
            $validated['dcDate'] = $request->input('dcDate') ?: now();  
        }

        $invoice = Invoice::create($validated);

        $invoice->load('project');

        return response()->json($invoice, 201);

    } catch (ValidationException $e) {
        return response()->json([
            'error' => 'Validation failed',
            'message' => $e->errors()
        ], 422);
    
    } catch (ModelNotFoundException $e) {
        // Handle case where the linked project doesn't exist
        return response()->json([
            'error' => 'Linked project not found',
            'message' => 'The specified linked project does not exist.'
        ], 404);

    } catch (Exception $e) {
        // Handle any other general errors
        return response()->json([
            'error' => 'Something went wrong',
            'message' => $e->getMessage()
        ], 500);
    }
}

    
public function show($id)
{
    $invoice = Invoice::with(['project', 'project.jcReferences', 'project.dcReferences'])
        ->findOrFail($id);

    $invoice->jcReferences = $invoice->project->jcReferences;
    $invoice->dcReferences = $invoice->project->dcReferences;

    return response()->json($invoice);
}


    public function update(Request $request, $id)
    {
        $invoice = Invoice::findOrFail($id);

        $validated = $request->validate([
            'invoiceReference' => 'string|max:255',
            'invoiceImage' => 'string|max:255',
            'amount' => 'numeric',
            'paymentTerms' => 'in:Cash,Credit',
            'creditDays' => 'nullable|string|max:255',
            'dueDate' => 'nullable|date',
            'linkedProject' => 'exists:projects,id',
            'status' => 'in:Paid,Unpaid,In Progress,Overdue,Cancelled',
            'clientName' => 'nullable|string|max:255',
            'poNumber' => 'nullable|string|max:255',
            'poDate' => 'nullable|string',
            'jcReference' => 'nullable|array',
            'jcDate' => 'nullable|string',
            'dcReference' => 'nullable|array',
            'dcDate' => 'nullable|string',
            'invoiceDate' => 'nullable|string',
        ]);
        if ($request->has('poNumber')) {
            $validated['poDate'] = $request->input('poDate') ?: now();  // Set to current date if no poDate provided
        }
    
        // Automatically set jcDate if jcReference is provided
        if ($request->has('jcReference')) {
            $validated['jcDate'] = $request->input('jcDate') ?: now();  // Set to current date if no jcDate provided
        }
    
        // Automatically set dcDate if dcReference is provided
        if ($request->has('dcReference')) {
            $validated['dcDate'] = $request->input('dcDate') ?: now();  // Set to current date if no dcDate provided
        }

        $invoice->update($validated);
        return response()->json($invoice->load('project'));
    }

    public function destroy($id)
    {
        $invoice = Invoice::findOrFail($id);
        $invoice->delete();

        return response()->json(['message' => 'Invoice deleted successfully.']);
    }
}
