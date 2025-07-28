<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Business;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Endroid\QrCode\QrCode as EndroidQrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeMode;
use Endroid\QrCode\Label\Label;

class BranchController extends Controller
{
    public function all()
    {
        $user = Auth::user();

        if ($user->hasRole('admin')) {
            $businesses = Business::all(['id', 'name']);
            $branches = Branch::with('business')->get();
        } else {
            // Only show businesses owned or managed by the user
            $businesses = Business::where('owner_id', $user->id)
                ->orWhereHas('admins', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                })
                ->get(['id', 'name']);

            if ($businesses->isEmpty()) {
                return Inertia::render('Branches/NoBusiness');
            }

            // Get branches for all businesses
            $branches = Branch::whereHas('business', function ($q) use ($user) {
                $q->where('owner_id', $user->id)
                  ->orWhereHas('admins', function ($q2) use ($user) {
                      $q2->where('user_id', $user->id);
                  });
            })->with('business')->get();
        }

        return Inertia::render('Branches/Index', [
            'business' => null,
            'branches' => $branches->map(function ($branch) {
                return [
                    'id' => $branch->id,
                    'name' => $branch->name,
                    'address' => $branch->address,
                    'phone' => $branch->phone,
                    'status' => $branch->status,
                    'business' => $branch->business ? [
                        'id' => $branch->business->id,
                        'name' => $branch->business->name
                    ] : null,
                ];
            }),
            'businesses' => $businesses
        ]);
    }

    public function index(Business $business)
    {
        $user = Auth::user();
        
        // Get all businesses the user has access to
        $businesses = Business::where('owner_id', $user->id)
            ->orWhereHas('admins', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->get(['id', 'name']);

        // Get branches for the specific business
        $branches = $business->branches()->with(['business'])->get();
        
        return Inertia::render('Branches/Index', [
            'business' => $business,
            'branches' => $branches->map(function ($branch) {
                return [
                    'id' => $branch->id,
                    'name' => $branch->name,
                    'address' => $branch->address,
                    'phone' => $branch->phone,
                    'status' => $branch->status,
                    'business' => $branch->business ? [
                        'id' => $branch->business->id,
                        'name' => $branch->business->name
                    ] : null,
                ];
            }),
            'businesses' => $businesses
        ]);
    }

    public function create(Business $business)
    {
        return Inertia::render('Branches/Create', [
            'business' => $business,
        ]);
    }

    public function store(Request $request, Business $business)
    {   $user = Auth::user();
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'gps_latitude' => 'nullable|numeric|between:-90,90',
            'gps_longitude' => 'nullable|numeric|between:-180,180',
            'phone' => 'required|string|max:50',
            'email' => 'required|email|max:255',
        ]);

        try {
            $branch = $business->branches()->create([
                'name' => $validated['name'],
                'address' => $validated['address'],
                'gps_latitude' => $validated['gps_latitude'] ?? null,
                'gps_longitude' => $validated['gps_longitude'] ?? null,
                'phone' => $validated['phone'],
                'email' => $validated['email'],
                'status' => 'active',
            ]);

            // Generate barcode for the new branch
            $branch->generateBarcode();
            $branch->refresh();

            // Always create a default store (location) for the branch
            
            \App\Models\Location::create([
                'name' => $branch->name . ' Store',
                'user_id'=>$user->id,
                'location_type_id' => 1, // store type
                'business_id' => $business->id,
                'branch_id' => $branch->id,
                'address' => $branch->address,
                'phone' => $branch->phone,
                'status' => 1,
            ]);

            return redirect()->route('businesses.branches.index', $business)
                ->with('success', 'Branch and default store created successfully.')
                ->with('branch', $branch);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to create branch: ' . $e->getMessage()]);
        }
    }

    public function storeWithStore(Request $request, Business $business)
    {   $user = Auth::user();
        $branchRules = [
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'gps_latitude' => 'nullable|numeric|between:-90,90',
            'gps_longitude' => 'nullable|numeric|between:-180,180',
            'phone' => 'required|string|max:50',
            'email' => 'required|email|max:255',
        ];
        $storeRules = [
            'store.name' => 'nullable|string|max:255',
            'store.address' => 'nullable|string',
            'store.phone' => 'nullable|string|max:50',
            'store.status' => 'nullable|in:0,1',
        ];
        $rules = array_merge($branchRules, $storeRules);
        $validated = $request->validate($rules);
        \DB::beginTransaction();
        try {
            $branch = $business->branches()->create([
                'name' => $validated['name'],
                'address' => $validated['address'],
                'gps_latitude' => $validated['gps_latitude'] ?? null,
                'gps_longitude' => $validated['gps_longitude'] ?? null,
                'phone' => $validated['phone'],
                'email' => $validated['email'],
                'status' => 'active',
            ]);
            $branch->generateBarcode();
            $branch->refresh();
            // Always create a store (location) for the branch
            \App\Models\Location::create([
                'name' => $validated['store']['name'] ?? ($branch->name . ' Store'),
                'user_id'=>$user->id,
                'location_type_id' => 1, // store type
                'business_id' => $business->id,
                'branch_id' => $branch->id,
                'address' => $validated['store']['address'] ?? $branch->address,
                'phone' => $validated['store']['phone'] ?? $branch->phone,
                'status' => $validated['store']['status'] ?? 1,
            ]);
            \DB::commit();
            return redirect()
                ->route('businesses.branches.index', $business)
                ->with('success', 'Branch and store created successfully.');
        } catch (\Exception $e) {
            \DB::rollBack();
            return redirect()->back()
                ->withErrors([
                    'error' => 'Failed to create branch and store: ' . $e->getMessage(),
                ])
                ->withInput();
        }
    }

    public function show(Business $business, Branch $branch)
    {
        // Check if user has access to this business
        if (!Auth::user()->canAccessBusiness($business->id)) {
            abort(403);
        }

        return Inertia::render('Branches/Show', [
            'business' => $business,
            'branch' => $branch->load('business', 'sellers')
        ]);
    }

    public function edit(Business $business, Branch $branch)
    {
        return Inertia::render('Branches/Edit', [
            'business' => $business,
            'branch' => $branch,
            'businesses' => Business::where('owner_id', Auth::id())->get(),
            'sellers' => $branch->sellers
        ]);
    }

    public function update(Request $request, Business $business, Branch $branch)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'gps_latitude' => 'nullable|numeric|between:-90,90',
            'gps_longitude' => 'nullable|numeric|between:-180,180',
            'phone' => 'required|string|max:50',
            'email' => 'required|email|max:255',
        ]);

        $branch->update([
            'name' => $validated['name'],
            'address' => $validated['address'],
            'gps_latitude' => $validated['gps_latitude'] ?? null,
            'gps_longitude' => $validated['gps_longitude'] ?? null,
            'phone' => $validated['phone'],
            'email' => $validated['email'],
        ]);

        $user = auth()->user();
        if ($user && $user->hasRole('admin')) {
            return redirect("/admin/branches/{$branch->id}")->with('success', 'Branch updated successfully.');
        } else {
            return redirect('/branches')->with('success', 'Branch updated successfully.');
        }
    }

    public function destroy(Business $business, Branch $branch)
    {
        // Only allow admins to delete branches
        if (!Auth::user()->hasRole('admin')) {
            abort(403, 'Unauthorized action. Only admins can delete branches.');
        }

        $branchName = $branch->name;
        $branch->delete();

        // Send email to branch contact and business owner
        try {
            \Mail::to($branch->email)->send(new \App\Mail\BranchDeletedMail($branchName, $business->name, auth()->user()));
            if ($business->owner && $business->owner->email !== $branch->email) {
                \Mail::to($business->owner->email)->send(new \App\Mail\BranchDeletedMail($branchName, $business->name, auth()->user()));
            }
        } catch (\Exception $e) {
            \Log::error('Failed to send branch deleted email: ' . $e->getMessage());
        }

        // If the request expects JSON (axios/fetch), return JSON for SweetAlert
        if (request()->expectsJson() || request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Branch deleted successfully.']);
        }

        $user = auth()->user();
        if ($user && $user->hasRole('admin')) {
            return redirect('/admin/branches')->with('success', 'Branch deleted successfully.');
        }
        return redirect()->route('branches.index', $business)
            ->with('success', 'Branch deleted successfully.');
    }

    public function downloadBarcode(Business $business, Branch $branch)
    {
        if (!$branch->barcode_path) {
            return response()->json(['error' => 'No barcode found'], 404);
        }

        // v6.0.8 syntax: set all options in the constructor
        $qrCode = new EndroidQrCode(
            $branch->barcode_path,
            new Encoding('UTF-8'),
            ErrorCorrectionLevel::High,
            300, // size
            10, // margin
            RoundBlockSizeMode::Margin,
            new Color(0, 0, 0), // foreground
            new Color(255, 255, 255) // background
        );

        $writer = new PngWriter();
        $result = $writer->write($qrCode);

        return response($result->getString(), 200)
            ->header('Content-Type', 'image/png')
            ->header('Content-Disposition', 'attachment; filename="barcode-' . $branch->barcode_path . '.png"');
    }

    public function printBarcode(Business $business, Branch $branch)
    {
        if (!$branch->barcode_path) {
            return response()->json(['error' => 'No barcode found'], 404);
        }

        // v6.0.8 syntax: set all options in the constructor
        $qrCode = new EndroidQrCode(
            $branch->barcode_path,
            new Encoding('UTF-8'),
            ErrorCorrectionLevel::High,
            300, // size
            10, // margin
            new RoundBlockSizeMode(RoundBlockSizeMode::Margin),
            new Color(0, 0, 0),
            new Color(255, 255, 255)
        );

        $writer = new PngWriter();
        $result = $writer->write($qrCode);

        return response($result->getString())
            ->header('Content-Type', $result->getMimeType())
            ->header('Content-Disposition', 'inline; filename="barcode.png"');
    }

    // Admin methods for viewing all branches
    public function adminIndex()
    {
        $this->checkAdminAccess();
        
        $branches = Branch::with(['business', 'sellers', 'products'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($branch) {
                return [
                    'id' => $branch->id,
                    'name' => $branch->name,
                    'address' => $branch->address,
                    'gps_latitude' => $branch->gps_latitude,
                    'gps_longitude' => $branch->gps_longitude,
                    'phone' => $branch->phone,
                    'email' => $branch->email,
                    'status' => $branch->status,
                    'barcode_path' => $branch->barcode_path,
                    'business' => $branch->business ? [
                        'id' => $branch->business->id,
                        'name' => $branch->business->name,
                        'owner' => $branch->business->owner ? [
                            'id' => $branch->business->owner->id,
                            'name' => $branch->business->owner->name,
                            'email' => $branch->business->owner->email,
                        ] : null,
                    ] : null,
                    'sellers_count' => $branch->sellers->count(),
                    'products_count' => $branch->products->count(),
                    'created_at' => $branch->created_at,
                    'updated_at' => $branch->updated_at,
                ];
            });

        return Inertia::render('Admin/Branches/Index', [
            'branches' => $branches,
        ]);
    }

    public function adminShow(Branch $branch)
    {
        $this->checkAdminAccess();
        $branch->refresh();
        $branch->load(['business.owner', 'sellers', 'products', 'business.admins']);
        return Inertia::render('Admin/Branches/Show', [
            'branch' => [
                'id' => $branch->id,
                'name' => $branch->name,
                'address' => $branch->address,
                'gps_latitude' => $branch->gps_latitude,
                'gps_longitude' => $branch->gps_longitude,
                'phone' => $branch->phone,
                'email' => $branch->email,
                'status' => $branch->status,
                'barcode_path' => $branch->barcode_path,
                'business' => $branch->business ? [
                    'id' => $branch->business->id,
                    'name' => $branch->business->name,
                    'description' => $branch->business->description,
                    'phone' => $branch->business->phone,
                    'email' => $branch->business->email,
                    'address' => $branch->business->address,
                    'city' => $branch->business->city,
                    'state' => $branch->business->state,
                    'country' => $branch->business->country,
                    'owner' => $branch->business->owner ? [
                        'id' => $branch->business->owner->id,
                        'name' => $branch->business->owner->name,
                        'email' => $branch->business->owner->email,
                        'created_at' => $branch->business->owner->created_at,
                    ] : null,
                    'admins' => $branch->business->admins->map(function ($admin) {
                        return [
                            'id' => $admin->id,
                            'name' => $admin->name,
                            'email' => $admin->email,
                        ];
                    }),
                ] : null,
                'sellers' => $branch->sellers->map(function ($seller) {
                    return [
                        'id' => $seller->id,
                        'name' => $seller->name,
                        'email' => $seller->email,
                        'phone' => $seller->phone,
                        'status' => $seller->status,
                        'created_at' => $seller->created_at,
                    ];
                }),
                'products' => $branch->products->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'description' => $product->description,
                        'price' => $product->price,
                        'stock_quantity' => $product->stock_quantity,
                        'status' => $product->status,
                        'created_at' => $product->created_at,
                    ];
                }),
                'created_at' => $branch->created_at,
                'updated_at' => $branch->updated_at,
            ],
        ]);
    }

    public function toggleStatus(Business $business, Branch $branch)
    {
        $branch->status = $branch->status === 'active' ? 'inactive' : 'active';
        $branch->save();
        return back()->with('success', 'Branch status updated.');
    }

    private function checkAdminAccess()
    {
        $user = auth()->user();
        if (!$user || !$user->hasRole('admin')) {
            abort(403, 'Access denied. Admin role required.');
        }
    }
}