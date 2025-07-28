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
        \Log::info('Customer creation request received', $request->all());
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'required|string|max:255',
            'address' => 'nullable|string',
        ]);

        $user = Auth::user();
        \Log::info('User authenticated', ['user_id' => $user->id, 'business_id' => $user->business_id, 'branch_id' => $user->branch_id]);

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

            \Log::info('Customer created successfully', ['customer_id' => $customer->id]);

            return response()->json([
                'success' => true,
                'message' => 'Customer created successfully!',
                'customer' => $customer
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to create customer', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to create customer. Please try again.',
                'error' => $e->getMessage()
            ], 422);
        }
    }
} 