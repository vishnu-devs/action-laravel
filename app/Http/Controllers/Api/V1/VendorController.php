<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\VendorRequest;
use App\Models\VendorDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class VendorController extends Controller
{
    public function submitVendorRequest(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'business_name' => 'required|string',
                'business_type' => 'required|string',
                'gst_number' => 'required|string',
                'pan_number' => 'required|string',
                'business_address' => 'required|string',
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
                'gst_certificate' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
                'pan_card' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
                'business_license' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
                'bank_statement' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Create vendor request
            $vendorRequest = VendorRequest::create([
                'user_id' => auth()->id(),
                'business_name' => $request->business_name,
                'business_type' => $request->business_type,
                'gst_number' => $request->gst_number,
                'pan_number' => $request->pan_number,
                'address' => $request->business_address,
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

            // Upload and attach documents
            $gstPath = $request->file('gst_certificate')->store('vendor_documents/gst', 'public');
            $panPath = $request->file('pan_card')->store('vendor_documents/pan', 'public');
            $licensePath = $request->file('business_license')->store('vendor_documents/license', 'public');
            $bankStatementPath = $request->file('bank_statement')->store('vendor_documents/bank', 'public');

            VendorDocument::create(['vendor_request_id' => $vendorRequest->id, 'document_type' => 'gst_certificate', 'path' => $gstPath]);
            VendorDocument::create(['vendor_request_id' => $vendorRequest->id, 'document_type' => 'pan_card', 'path' => $panPath]);
            VendorDocument::create(['vendor_request_id' => $vendorRequest->id, 'document_type' => 'business_license', 'path' => $licensePath]);
            VendorDocument::create(['vendor_request_id' => $vendorRequest->id, 'document_type' => 'bank_statement', 'path' => $bankStatementPath]);



            return response()->json([
                'status' => 'success',
                'message' => 'Vendor request submitted successfully',
                'data' => $vendorRequest
            ], 201);

        } catch (\Exception $e) {
            \Log::error('Vendor request submission failed: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to submit vendor request'
            ], 500);
        }
    }

    public function getVendorDetails()
    {
        try {
            $vendorRequest = VendorRequest::with('documents')->where('user_id', auth()->id())->latest()->first();
            
            if (!$vendorRequest) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Vendor details not found'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => $vendorRequest
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to get vendor details: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get vendor details'
            ], 500);
        }
    }

    public function updateVendorRequestStatus(Request $request, $vendorRequestId)
    {
        try {
            $request->validate([
                'status' => 'required|in:0,1,2',
                'rejection_reason' => 'nullable|string'
            ]);

            $vendorRequest = VendorRequest::where('id', $vendorRequestId)
                ->where('user_id', auth()->id())
                ->first();

            if (!$vendorRequest) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Vendor request not found'
                ], 404);
            }

            $updateData = [
                'status' => (int) $request->status,
            ];

            if ($request->status === '1') {
                $updateData['approved_at'] = now();
            } elseif ($request->status === '2') {
                $updateData['rejection_reason'] = $request->rejection_reason;
            } else {
                $updateData['approved_at'] = null;
                $updateData['rejection_reason'] = null;
            }

        $vendorRequest->update($updateData);

            // If approved, assign vendor role to user
            if ($request->status === '1') {
                $vendorRequest->user->assignRole('vendor');
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Vendor request status updated successfully',
                'data' => $vendorRequest
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to update vendor request status: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update vendor request status'
            ], 500);
        }
    }
}