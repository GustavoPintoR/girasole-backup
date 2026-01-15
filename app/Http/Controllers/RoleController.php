<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Role::class);

        $validated = $request->validate([
            'per_page' => 'nullable|integer|min:1',
            'sort_by' => 'nullable|in:name,created_at',
            'sort_order' => 'nullable|in:asc,desc',
            'search' => 'nullable|string|max:255',
        ]);

        $perPage = $validated['per_page'] ?? 10;
        $sortBy = $validated['sort_by'] ?? 'created_at';
        $sortOrder = $validated['sort_order'] ?? 'asc';
        $search = $validated['search'] ?? null;

        $searchableColumns = ['name'];

        $query = Role::query();

        if ($search) {
            $query->where(function ($q) use ($search, $searchableColumns) {
                foreach ($searchableColumns as $column) {
                    $q->orWhere($column, 'ilike', '%'.$search.'%');
                }
            });
        }

        $roles = $query
            ->orderBy($sortBy, $sortOrder)
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('roles/Index', [
            'roles' => $roles,
            'sorting' => compact('sortBy', 'sortOrder'),
            'filtering' => [
                'search' => $search,
                'searchableColumns' => $searchableColumns,
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        Gate::authorize('create', Role::class);

        $permissions = Permission::all();
        $permissionGroups = $permissions->groupBy(function ($permission) {
            $parts = explode('_', $permission->name, 2);
            return $parts[1] ?? 'others';
        })->map(function ($group) {
            return $group->pluck('name')->keyBy(function ($name) {
                $parts = explode('_', $name, 2);
                return $parts[0];
            })->toArray();
        })->toArray();

        return Inertia::render('roles/Create', ['permissionGroups' => $permissionGroups]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Role::class);
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:roles,name',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $validated = $validator->validated();

        $role = Role::create(['name' => strtolower(Str::snake($validated['name']))]);
        $role->syncPermissions($validated['permissions'] ?? []);

        return redirect()->route('roles.index')
            ->with('success', 'Role created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Role $role): Response
    {
        Gate::authorize('view', $role);

        $permissions = Permission::all();
        $permissionGroups = $permissions->groupBy(function ($permission) {
            $parts = explode('_', $permission->name, 2);
            return $parts[1] ?? 'others';
        })->map(function ($group) {
            return $group->pluck('name')->keyBy(function ($name) {
                $parts = explode('_', $name, 2);
                return $parts[0];
            })->toArray();
        })->toArray();

        return Inertia::render('roles/Show', [
            'role' => $role->load('permissions'),
            'permissionGroups' => $permissionGroups,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Role $role): Response
    {
        Gate::authorize('update', $role);

        $permissions = Permission::all();
        $permissionGroups = $permissions->groupBy(function ($permission) {
            $parts = explode('_', $permission->name, 2);
            return $parts[1] ?? 'others';
        })->map(function ($group) {
            return $group->pluck('name')->keyBy(function ($name) {
                $parts = explode('_', $name, 2);
                return $parts[0];
            })->toArray();
        })->toArray();

        return Inertia::render('roles/Edit', [
            'role' => $role->load('permissions'),
            'permissionGroups' => $permissionGroups,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Role $role): RedirectResponse
    {
        Gate::authorize('update', $role);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:roles,name,'.$role->id,
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $validated = $validator->validated();

        $role->update(['name' => strtolower(Str::snake($validated['name']))]);
        $role->syncPermissions($validated['permissions'] ?? []);

        return redirect()->route('roles.index')
            ->with('success', 'Role updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role): RedirectResponse
    {
        Gate::authorize('delete', $role);

        try {
            $role->delete();

            return redirect()->route('roles.index')
                ->with('success', __('ui.deleted_successfully', ['resource' => __('ui.roles')]));
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }
}
