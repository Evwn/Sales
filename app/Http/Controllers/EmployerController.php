<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class EmployerController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        // Only allow owners
        abort_unless($user->hasRole('owner'), 403);
        // Get all businesses owned by the current user
        $ownedBusinessIds = \App\Models\Business::where('owner_id', $user->id)->pluck('id');
        // Get all users for those businesses, and always include the owner (logged-in user), excluding admins
        $employers = \App\Models\User::where(function ($q) use ($ownedBusinessIds, $user) {
                $q->whereIn('business_id', $ownedBusinessIds)
                  ->orWhere('id', $user->id); // include the owner themselves
            })
            ->whereDoesntHave('roles', function ($q) {
                $q->where('name', 'admin');
            })
            ->with(['roles'])
            ->get()
            ->map(function ($employer) {
                return [
                    'id' => $employer->id,
                    'name' => $employer->name,
                    'email' => $employer->email,
                    'phone' => $employer->phone ?? '-',
                    'role' => $employer->roles->pluck('name')->first() ?? '-',
                ];
            });
        return Inertia::render('Employers/Index', [
            'employers' => $employers,
        ]);
    }

    public function create()
    {
        $user = auth()->user();

        // Get roles for this owner or system roles, only for pos/backoffice, exclude admin/owner
        $roles = \App\Models\Role::whereIn('guard_name', ['pos', 'backoffice'])
            ->whereNotIn('name', ['admin', 'owner'])
            ->where(function ($q) use ($user) {
                $q->whereNull('owner_id')->orWhere('owner_id', $user->id);
            })
            ->get(['id', 'name', 'guard_name'])
            ->groupBy('name')
            ->map(function ($roleGroup, $roleName) {
                return [
                    'name' => $roleName,
                    'guards' => $roleGroup->pluck('guard_name')->unique()->values()->toArray(),
                    'id' => $roleGroup->first()->id, // for selection
                ];
            })
            ->values();

        // Get businesses owned by this user
        $businesses = \App\Models\Business::where('owner_id', $user->id)->get(['id', 'name']);

        // Get all branches for those businesses
        $branches = \App\Models\Branch::whereIn('business_id', $businesses->pluck('id'))->get(['id', 'name', 'business_id']);

        return inertia('Employers/Create', [
            'roles' => $roles,
            'businesses' => $businesses,
            'branches' => $branches,
        ]);
    }

    public function accessControl()
    {
        $user = auth()->user();
        abort_unless($user->hasRole('owner'), 403);

        // Fetch system roles (owner_id is null) and custom roles for this owner
        $roles = \Spatie\Permission\Models\Role::whereIn('guard_name', ['pos', 'backoffice'])
            ->whereNotIn('name', ['admin', 'owner'])
            ->where(function ($q) use ($user) {
                $q->whereNull('owner_id')->orWhere('owner_id', $user->id);
            })
            ->get();

        // Get all businesses owned by the current user
        $ownedBusinessIds = \App\Models\Business::where('owner_id', $user->id)->pluck('id');

        $rolesData = $roles->groupBy('name')->map(function ($roleGroup, $roleName) use ($ownedBusinessIds) {
            $guards = $roleGroup->pluck('guard_name')->unique()->values()->toArray();
            $employees = \App\Models\User::whereIn('business_id', $ownedBusinessIds)
                ->whereHas('roles', function ($q) use ($roleName) {
                    $q->where('name', $roleName);
                })->count();
            return [
                'name' => ucfirst($roleName),
                'guards' => $guards,
                'employees' => $employees,
            ];
        })->values();

        return inertia('Employers/AccessControl', [
            'roles' => $rolesData,
        ]);
    }

    public function createRole()
    {
        // Fetch all permissions and group by guard_name
        $permissions = \Spatie\Permission\Models\Permission::all(['id', 'name', 'guard_name']);
        $permissionsByGuard = $permissions->groupBy('guard_name')->map(function ($perms) {
            return $perms->pluck('name');
        });
        return inertia('Employers/Roles/Create', [
            'permissions' => $permissionsByGuard,
        ]);
    }

    public function storeRole(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'guards' => 'required|array|min:1',
            'permissions' => 'required|array',
        ]);

        $ownerId = auth()->id();

        try {
            foreach ($request->guards as $guard) {
                $role = \Spatie\Permission\Models\Role::create([
                    'name' => $request->name,
                    'guard_name' => $guard,
                    'owner_id' => $ownerId,
                ]);
                $perms = $request->permissions[$guard] ?? [];
                $role->syncPermissions($perms);
            }
            return redirect()->route('employers.accessControl')->with('success', 'Role created!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to create role: ' . $e->getMessage());
        }
    }

    public function editRole($roleName)
    {
        $user = auth()->user();
        abort_unless($user->hasRole('owner'), 403);

        // Fetch all roles with this name for this owner (or system)
        $roles = \Spatie\Permission\Models\Role::where('name', $roleName)
            ->whereIn('guard_name', ['pos', 'backoffice'])
            ->where(function ($q) use ($user) {
                $q->whereNull('owner_id')->orWhere('owner_id', $user->id);
            })
            ->get();

        // Get guards and permissions for this role
        $guards = $roles->pluck('guard_name')->unique()->values()->toArray();
        $rolePermissions = [];
        foreach ($roles as $role) {
            $rolePermissions[$role->guard_name] = $role->permissions->pluck('name')->toArray();
        }

        // Fetch all permissions grouped by guard
        $permissions = \Spatie\Permission\Models\Permission::all(['id', 'name', 'guard_name']);
        $permissionsByGuard = $permissions->groupBy('guard_name')->map(function ($perms) {
            return $perms->pluck('name');
        });

        return inertia('Employers/Roles/Edit', [
            'role' => [
                'name' => $roleName,
                'guards' => $guards,
                'permissions' => $rolePermissions,
            ],
            'permissions' => $permissionsByGuard,
        ]);
    }

    public function updateRole(Request $request, $roleName)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'guards' => 'required|array|min:1',
            'permissions' => 'required|array',
        ]);

        $user = auth()->user();

        try {
            // Update or create for each guard
            foreach ($request->guards as $guard) {
                $role = \Spatie\Permission\Models\Role::where('name', $roleName)
                    ->where('guard_name', $guard)
                    ->where(function ($q) use ($user) {
                        $q->whereNull('owner_id')->orWhere('owner_id', $user->id);
                    })
                    ->first();

                if ($role) {
                    $role->name = $request->name;
                    $role->save();
                } else {
                    $role = \Spatie\Permission\Models\Role::create([
                        'name' => $request->name,
                        'guard_name' => $guard,
                        'owner_id' => $user->id,
                    ]);
                }
                $perms = $request->permissions[$guard] ?? [];
                $role->syncPermissions($perms);
            }

            // Remove roles for guards that were unchecked
            $allGuards = ['pos', 'backoffice'];
            $toRemove = array_diff($allGuards, $request->guards);
            foreach ($toRemove as $guard) {
                \Spatie\Permission\Models\Role::where('name', $roleName)
                    ->where('guard_name', $guard)
                    ->where(function ($q) use ($user) {
                        $q->whereNull('owner_id')->orWhere('owner_id', $user->id);
                    })
                    ->delete();
            }

            return redirect()->route('employers.accessControl')->with('success', 'Role updated!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update role: ' . $e->getMessage());
        }
    }

    public function store(\Illuminate\Http\Request $request)
    {
        $user = auth()->user();
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'nullable|string|max:255',
            'role' => 'required|string',
            'business_id' => 'required|exists:businesses,id', // enforce required
            'branch_id' => 'required|exists:branches,id',
            'password' => ['nullable', 'string', 'min:8', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\\d)(?=.*[^a-zA-Z\\d]).+$/'],
            'pin_code' => ['nullable', 'digits:4', 'regex:/^\\d{4}$/', 'unique:users,pin_code,NULL,id,business_id,' . $request->business_id],
        ], [
            'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one symbol.',
            'pin_code.digits' => 'PIN code must be exactly 4 digits.',
            'pin_code.regex' => 'PIN code must be exactly 4 digits.',
        ]);

        if (empty($validated['password']) && empty($validated['pin_code'])) {
            return back()->withErrors(['error' => 'Please provide either a password or a 4-digit PIN code.'])->withInput();
        }

        \DB::beginTransaction();
        try {
            // If no password, generate a strong one
            $password = $validated['password'] ?? \Str::random(12) . 'aA1!';
            $userData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'business_id' => $validated['business_id'],
                'branch_id' => $validated['branch_id'],
                'password' => \Hash::make($password),
            ];
            if (!empty($validated['pin_code'])) {
                $userData['pin_code'] = $validated['pin_code'];
            }
            $newUser = \App\Models\User::create($userData);
            // Assign role by name (for both pos and backoffice guards if present)
            $roleName = $validated['role'];
            $roles = \Spatie\Permission\Models\Role::where('name', $roleName)
                ->whereIn('guard_name', ['pos', 'backoffice'])
                ->where(function ($q) use ($user) {
                    $q->whereNull('owner_id')->orWhere('owner_id', $user->id);
                })->get();
            foreach ($roles as $role) {
                $newUser->assignRole($role);
            }
            // Send email verification
            $newUser->sendEmailVerificationNotification();
            // Send email to the new user
            \Mail::raw(
                "Hello {$newUser->name},\n\nYou have been registered as a {$roleName} in our system.\nFor more information, please contact your administrator.",
                function ($message) use ($newUser) {
                    $message->to($newUser->email)
                        ->subject('You have been registered');
                }
            );
            \DB::commit();
            return redirect()->route('employers.create')->with('success', 'Employee created successfully!');
        } catch (\Exception $e) {
            \DB::rollBack();
            return back()->withErrors(['error' => 'Failed to create employee: ' . $e->getMessage()])->withInput();
        }
    }
} 