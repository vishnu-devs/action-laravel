<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\VendorRequest;
use Illuminate\Http\Request;

class VendorRequestController extends Controller
{
    public function create()
    {
        // Check if user already has a pending request
        $pendingRequest = VendorRequest::where('user_id', auth()->id())
            ->where('status', 0)
            ->first();
            
        if ($pendingRequest) {
            return redirect()->route('dashboard')
                ->with('info', 'You already have a pending vendor request');
        }
        
        return view('vendor-request.create');
    }

    public function store(Request $request)
    {
        // Check if user already has a pending request
        $pendingRequest = VendorRequest::where('user_id', auth()->id())
            ->where('status', 0)
            ->first();
            
        if ($pendingRequest) {
            return redirect()->route('dashboard')
                ->with('info', 'You already have a pending vendor request');
        }

        $request->validate([
            'company_name' => 'required|string|max:255',
            'company_address' => 'required|string',
            'business_type' => 'required|string|max:255',
            'gst_number' => 'required|string|max:15',
        ]);

        VendorRequest::create([
            'user_id' => auth()->id(),
            'company_name' => $request->company_name,
            'company_address' => $request->company_address,
            'business_type' => $request->business_type,
            'gst_number' => $request->gst_number,
            'status' => 0
        ]);

        return redirect()->route('dashboard')
            ->with('success', 'Vendor request submitted successfully');
    }
}