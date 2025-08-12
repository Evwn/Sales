<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CustomerController extends Controller
{
    public function store(Request $request)
    {
        
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'nullable|email|max:255',
                'phone' => 'required|string|max:255',
                'address' => 'nullable|string',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {

        }

        $user = Auth::user();

        try {
            $customer = Customer::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'business_id' => $user->business_id,
                'branch_id' => $user->branch_id,
                'status' => 1,
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Failed to create customer. Please try again.',
                'error' => $e->getMessage()
            ], 422);
        }
    }
} 