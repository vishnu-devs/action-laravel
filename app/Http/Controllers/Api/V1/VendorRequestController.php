<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\VendorRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class VendorRequestController extends Controller
{
    public function index()
    {
        $vendorRequests = VendorRequest::with(['user', 'documents'])->paginate(10);

        return response()->json([
            'status' => 'success',
            'data' => $vendorRequests->map(function ($request) {
                return [
                    'id' => $request->id,
                    'user' => [
                        'id' => $request->user->id,
                        'name' => $request->user->name,
                        'email' => $request->user->email
                    ],
                    'business_name' => $request->business_name,
                    'business_type' => $request->business_type,
                    'status' => $request->status,
                    'created_at' => [
                        'raw' => $request->created_at,
                        'formatted' => $request->created_at->format('d M Y, h:i A')
                    ]
                ];
            }),
            'pagination' => [
                'total' => $vendorRequests->total(),
                'per_page' => $vendorRequests->perPage(),
                'current_page' => $vendorRequests->currentPage(),
                'last_page' => $vendorRequests->lastPage()
            ]
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'business_name' => 'required|string|max:255',
            'business_type' => 'required|string|max:255',
            'gst_number' => 'required|string|max:255',
            'pan_number' => 'required|string|max:255',
            'address' => 'required|string',
            'city' => 'required|string',
            'state' => 'required|string',
            'pincode' => 'required|string',
            'contact_person_name' => 'required|string',
            'contact_person_phone' => 'required|string',
            'alternate_phone' => 'nullable|string',
            'bank_name' => 'required|string',
            'account_number' => 'required|string',
            'ifsc_code' => 'required|string',
            'branch_name' => 'required|string',
            'documents' => 'nullable|array',
            'documents.*' => 'file|mimes:pdf,jpg,jpeg,png|max:2048',
            'document_types' => 'nullable|array',
            'document_types.*' => 'string'
        ]);

        $vendorRequest = VendorRequest::create([
            'user_id' => auth()->id(),
            'business_name' => $request->business_name,
            'business_type' => $request->business_type,
            'gst_number' => $request->gst_number,
            'pan_number' => $request->pan_number,
            'address' => $request->address,
            'city' => $request->city,
            'state' => $request->state,
            'pincode' => $request->pincode,
            'contact_person_name' => $request->contact_person_name,
            'contact_person_phone' => $request->contact_person_phone,
            'alternate_phone' => $request->alternate_phone,
            'bank_name' => $request->bank_name,
            'account_number' => $request->account_number,
            'ifsc_code' => $request->ifsc_code,
            'branch_name' => $request->branch_name,
            'status' => 0
        ]);

        if ($request->hasFile('documents')) {
            $files = $request->file('documents');
            $types = $request->input('document_types', []);
            foreach ($files as $idx => $document) {
                $type = $types[$idx] ?? 'other';
                $dir = 'vendor_documents/' . $type;
                $path = $document->store($dir, 'public');
                $vendorRequest->documents()->create([
                    'document_type' => $type,
                    'path' => $path,
                ]);
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Vendor request submitted successfully',
            'data' => $vendorRequest
        ], 201);
    }

    public function show(VendorRequest $vendorRequest)
    {
        // Check if user is authorized to view this request
        if (auth()->id() !== $vendorRequest->user_id && !auth()->user()->hasRole(['admin','super_admin'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 403);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $vendorRequest->id,
                'user' => [
                    'id' => $vendorRequest->user->id,
                    'name' => $vendorRequest->user->name,
                    'email' => $vendorRequest->user->email
                ],
                'business_name' => $vendorRequest->business_name,
                'business_type' => $vendorRequest->business_type,
                'gst_number' => $vendorRequest->gst_number,
                'pan_number' => $vendorRequest->pan_number,
                'address' => $vendorRequest->address,
                'status' => $vendorRequest->status,
                'documents' => $vendorRequest->documents->map(function($document) {
                    return [
                        'id' => $document->id,
                        'url' => asset('storage/' . $document->path)
                    ];
                }),
                'created_at' => [
                    'raw' => $vendorRequest->created_at,
                    'formatted' => $vendorRequest->created_at->format('d M Y, h:i A')
                ]
            ]
        ]);
    }

    public function update(Request $request, VendorRequest $vendorRequest)
    {
        // Only admin can update vendor request status
        if (!auth()->user()->hasRole('admin')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 403);
        }

        $request->validate([
            'status' => 'required|in:0,1,2',
            'rejection_reason' => 'required_if:status,2|string'
        ]);

        $update = [
            'status' => (int) $request->status,
            'rejection_reason' => $request->status == '2' ? $request->rejection_reason : null,
        ];
        if ($request->status == '1') {
            $update['approved_at'] = now();
        } else {
            $update['approved_at'] = null;
        }
        $vendorRequest->update($update);

        // If approved, assign vendor role to user
        if ($request->status === '1') {
            $vendorRequest->user->assignRole('vendor');
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Vendor request updated successfully',
            'data' => $vendorRequest
        ]);
    }

    public function destroy(VendorRequest $vendorRequest)
    {
        // Check if user is authorized to delete this request
        if (auth()->id() !== $vendorRequest->user_id && !auth()->user()->hasRole('admin')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 403);
        }

        // Delete documents from storage
        foreach ($vendorRequest->documents as $document) {
            Storage::disk('public')->delete($document->path);
            $document->delete();
        }

        $vendorRequest->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Vendor request deleted successfully'
        ]);
    }
}