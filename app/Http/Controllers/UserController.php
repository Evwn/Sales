<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Business;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    private function checkAdminAccess()
    {
        $user = auth()->user();
        if (!$user || !$user->hasRole('admin')) {
            abort(403, 'Access denied. Admin role required.');
        }
    }

    public function index()
    {
        $this->checkAdminAccess();
        
        $users = User::with(['roles.permissions', 'permissions', 'business', 'branch', 'ownedBusinesses'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($user) {
                // For owners, get all their businesses and branches
                $businesses = [];
                $branches = [];
                
                if ($user->hasRole('owner')) {
                    $ownedBusinesses = $user->ownedBusinesses()->with('branches')->get();
                    $businesses = $ownedBusinesses->map(function ($business) {
                        return [
                            'id' => $business->id,
                            'name' => $business->name,
                        ];
                    });
                    $branches = $ownedBusinesses->flatMap(function ($business) {
                        return $business->branches->map(function ($branch) {
                            return [
                                'id' => $branch->id,
                                'name' => $branch->name,
                                'business_name' => $branch->business->name,
                            ];
                        });
                    });
                } else {
                    // For non-owners, show single business/branch assignment
                    if ($user->business) {
                        $businesses = [[
                            'id' => $user->business->id,
                            'name' => $user->business->name,
                        ]];
                    }
                    if ($user->branch) {
                        $branches = [[
                            'id' => $user->branch->id,
                            'name' => $user->branch->name,
                            'business_name' => $user->branch->business->name,
                        ]];
                    }
                }

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'email_verified_at' => $user->email_verified_at,
                    'business' => $user->business ? [
                        'id' => $user->business->id,
                        'name' => $user->business->name,
                    ] : null,
                    'branch' => $user->branch ? [
                        'id' => $user->branch->id,
                        'name' => $user->branch->name,
                    ] : null,
                    'businesses' => $businesses,
                    'branches' => $branches,
                    'roles' => $user->roles->map(function ($role) {
                        return [
                            'id' => $role->id,
                            'name' => $role->name,
                        ];
                    }),
                    'permissions' => $user->permissions->map(function ($permission) {
                        return [
                            'id' => $permission->id,
                            'name' => $permission->name,
                        ];
                    }),
                    'all_permissions' => $user->getAllPermissions()->map(function ($permission) {
                        return [
                            'id' => $permission->id,
                            'name' => $permission->name,
                        ];
                    }),
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                    'is_online' => $user->is_online,
                    'last_seen_at' => $user->last_seen_at,
                ];
            });

        $roles = Role::with('permissions')->get()->map(function ($role) {
            return [
                'id' => $role->id,
                'name' => $role->name,
                'permissions' => $role->permissions->map(function ($permission) {
                    return [
                        'id' => $permission->id,
                        'name' => $permission->name,
                    ];
                }),
            ];
        });

        $permissions = Permission::all()->map(function ($permission) {
            return [
                'id' => $permission->id,
                'name' => $permission->name,
            ];
        });

        $businesses = Business::all(['id', 'name']);
        $branches = Branch::with('business')->get(['id', 'name', 'business_id']);

        return Inertia::render('Users/Index', [
            'users' => $users,
            'roles' => $roles,
            'permissions' => $permissions,
            'businesses' => $businesses,
            'branches' => $branches,
        ]);
    }

    public function store(Request $request)
    {
        $this->checkAdminAccess();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role_ids' => 'required|array|min:1',
            'role_ids.*' => 'exists:roles,id',
            'business_id' => 'nullable|exists:businesses,id',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        // Get the roles to check if they are admin (only admin should not have business/branch)
        $roles = Role::whereIn('id', $validated['role_ids'])->get();
        $isAdmin = $roles->where('name', 'admin')->count() > 0;

        DB::beginTransaction();
        try {
            $userData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'email_verified_at' => now(),
            ];

            // Only set business_id and branch_id if the user is not admin
            // Owners can have businesses and branches assigned
            if (!$isAdmin) {
                $userData['business_id'] = $validated['business_id'];
                $userData['branch_id'] = $validated['branch_id'];
            }

            $user = User::create($userData);
            $user->assignRole($roles);

            $roleNames = $roles->pluck('name')->toArray();
            if (in_array('owner', $roleNames)) {
                Mail::to($user->email)->send(new \App\Mail\OwnerWelcomeMail($user));
            } elseif (in_array('seller', $roleNames)) {
                $branch = $user->branch;
                $business = $user->business;
                Mail::to($user->email)->send(new \App\Mail\SellerWelcomeMail($user, $branch, $business));
            } elseif (in_array('admin', $roleNames)) {
                Mail::to($user->email)->send(new \App\Mail\AdminWelcomeMail($user));
            } elseif (in_array('customer', $roleNames)) {
                Mail::to($user->email)->send(new \App\Mail\CustomerWelcomeMail($user));
            } elseif (in_array('supplier', $roleNames)) {
                Mail::to($user->email)->send(new \App\Mail\SupplierWelcomeMail($user));
            } else {
                // Fallback generic welcome
                Mail::to($user->email)->send(new \App\Mail\OwnerWelcomeMail($user));
            }

            DB::commit();

            return redirect()->route('users.index')->with('success', 'User created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to create user: ' . $e->getMessage()]);
        }
    }

    public function update(Request $request, User $user)
    {
        $this->checkAdminAccess();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'role_ids' => 'required|array|min:1',
            'role_ids.*' => 'exists:roles,id',
            'business_id' => 'nullable|exists:businesses,id',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        // Get the roles to check if they are admin or owner (should not have business/branch)
        $roles = Role::whereIn('id', $validated['role_ids'])->get();
        $isAdmin = $roles->where('name', 'admin')->count() > 0;
        $isOwner = $roles->where('name', 'owner')->count() > 0;

        DB::beginTransaction();
        try {
            $userData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
            ];

            // Only set business_id and branch_id if the user is not admin or owner
            if (!$isAdmin && !$isOwner) {
                $userData['business_id'] = $request->filled('business_id') && $validated['business_id'] ? $validated['business_id'] : null;
                $userData['branch_id'] = $request->filled('branch_id') && $validated['branch_id'] ? $validated['branch_id'] : null;
            } else {
                // Clear business and branch for admin and owner roles
                $userData['business_id'] = null;
                $userData['branch_id'] = null;
            }

            $user->update($userData);
            $user->syncRoles($roles);

            // Build a professional, detailed summary for the email
            $summary = '';

            // Track changes
            $changes = [];
            if ($user->wasChanged('name')) {
                $changes['name'] = [
                    'old' => $user->getOriginal('name'),
                    'new' => $user->name
                ];
            }
            if ($user->wasChanged('email')) {
                $changes['email'] = [
                    'old' => $user->getOriginal('email'),
                    'new' => $user->email
                ];
            }
            if ($user->wasChanged('business_id')) {
                $oldBusiness = $user->getOriginal('business_id');
                $newBusiness = $user->business_id;
                $changes['business'] = [
                    'old' => $oldBusiness,
                    'new' => $newBusiness
                ];
            }
            if ($user->wasChanged('branch_id')) {
                $oldBranch = $user->getOriginal('branch_id');
                $newBranch = $user->branch_id;
                $changes['branch'] = [
                    'old' => $oldBranch,
                    'new' => $newBranch
                ];
            }
            $oldRoles = $user->roles->pluck('name')->toArray();
            $newRoles = $roles->pluck('name')->toArray();
            if ($oldRoles !== $newRoles) {
                $changes['roles'] = [
                    'old' => implode(', ', $oldRoles),
                    'new' => implode(', ', $newRoles)
                ];
            }

            if (!empty($changes)) {
                $summary = "Your profile was updated by an administrator. The following information was changed:\n\n Your profile was changed to\n\n";
                if (isset($changes['name'])) {
                    $summary .= "- Name: {$user->name}\n";
                }
                if (isset($changes['email'])) {
                    $summary .= "- Email: {$user->email}\n";
                }
                if (isset($changes['business'])) {
                    $businessName = $user->business_id ? (\App\Models\Business::find($user->business_id)->name ?? 'None') : 'None';
                    $summary .= "- Business: $businessName\n";
                }
                if (isset($changes['branch'])) {
                    $branchName = $user->branch_id ? (\App\Models\Branch::find($user->branch_id)->name ?? 'None') : 'None';
                    $summary .= "- Branch: $branchName\n";
                }
                if (isset($changes['roles'])) {
                    $summary .= "- Roles: " . implode(', ', $roles->pluck('name')->toArray()) . "\n";
                }
                $summary .= "\nIf you did not request or expect these changes, please contact support immediately.";
                Mail::to($user->email)->send(new \App\Mail\ProfileChangedMail($user, $summary));
            }

            DB::commit();

            return redirect()->route('users.index')->with('success', 'User updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to update user: ' . $e->getMessage()]);
        }
    }

    public function updatePassword(Request $request, User $user)
    {
        $this->checkAdminAccess();
        
        $validated = $request->validate([
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        try {
            $user->update([
                'password' => Hash::make($validated['password']),
            ]);

            return redirect()->route('users.index')->with('success', 'Password updated successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to update password: ' . $e->getMessage()]);
        }
    }

    public function destroy(User $user)
    {
        $this->checkAdminAccess();
        
        // Prevent admin from deleting themselves
        if ($user->id === auth()->id()) {
            return back()->withErrors(['error' => 'You cannot delete your own account.']);
        }

        try {
            $user->delete();
            return redirect()->route('users.index')->with('success', 'User deleted successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to delete user: ' . $e->getMessage()]);
        }
    }

    public function toggleStatus(User $user)
    {
        $this->checkAdminAccess();
        
        try {
            // Toggle the online status for the specific user
            $newStatus = !$user->is_online;
            $user->update([
                'is_online' => $newStatus,
            ]);

            // Send email notification to the user
            $statusString = $newStatus ? 'activated' : 'deactivated';
            \Mail::to($user->email)->send(new \App\Mail\EmailStatusChangedMail($user, $statusString));

            $message = $newStatus 
                ? "User {$user->name} ({$user->email}) activated successfully." 
                : "User {$user->name} ({$user->email}) deactivated successfully.";
            
            return redirect()->route('users.index')->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->route('users.index')->with('error', 'Failed to update user status: ' . $e->getMessage());
        }
    }

    public function assignPermissions(Request $request, User $user)
    {
        $this->checkAdminAccess();
        
        $validated = $request->validate([
            'permission_ids' => 'array',
            'permission_ids.*' => 'exists:permissions,id',
        ]);

        try {
            $permissions = Permission::whereIn('id', $validated['permission_ids'] ?? [])->get();
            $user->syncPermissions($permissions);

            return redirect()->route('users.index')->with('success', 'Permissions assigned successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to assign permissions: ' . $e->getMessage()]);
        }
    }

    public function show(User $user)
    {
        $this->checkAdminAccess();
        
        $user->load(['roles', 'permissions', 'business', 'branch']);

        return Inertia::render('Users/Show', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'email_verified_at' => $user->email_verified_at,
                'business' => $user->business ? [
                    'id' => $user->business->id,
                    'name' => $user->business->name,
                ] : null,
                'branch' => $user->branch ? [
                    'id' => $user->branch->id,
                    'name' => $user->branch->name,
                ] : null,
                'roles' => $user->roles->map(function ($role) {
                    return [
                        'id' => $role->id,
                        'name' => $role->name,
                    ];
                }),
                'permissions' => $user->permissions->map(function ($permission) {
                    return [
                        'id' => $permission->id,
                        'name' => $permission->name,
                    ];
                }),
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
                'is_online' => $user->is_online,
                'last_seen_at' => $user->last_seen_at,
            ],
        ]);
    }

    public function toggleEmailVerification(User $user)
    {
        $this->checkAdminAccess();
        
        try {
            // Toggle the email verification status for the specific user
            $newStatus = $user->email_verified_at ? null : now();
            $user->update([
                'email_verified_at' => $newStatus,
            ]);

            $statusString = $newStatus ? 'verified' : 'unverified';
            \Mail::to($user->email)->send(new \App\Mail\EmailVerificationStatusChangedMail($user, $statusString));

            $message = $newStatus 
                ? "Email for {$user->name} ({$user->email}) verified successfully." 
                : "Email for {$user->name} ({$user->email}) unverified successfully.";
            
            return response()->json([
                'success' => true,
                'message' => $message,
                'email_verified_at' => $newStatus,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update email verification status: ' . $e->getMessage(),
            ], 500);
        }
    }
} 