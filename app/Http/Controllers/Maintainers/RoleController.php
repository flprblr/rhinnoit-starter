<?php

namespace App\Http\Controllers\Maintainers;

use App\Exports\RolesExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Maintainers\Roles\StoreRoleRequest;
use App\Http\Requests\Maintainers\Roles\UpdateRoleRequest;
use App\Imports\RolesImport;
use App\Models\Permission;
use App\Models\Role;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Role::class, 'role');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $allowedSortColumns = ['id', 'name', 'created_at', 'updated_at'];
        $allowedSortDirections = ['asc', 'desc'];
        $defaultPerPage = 10;
        $maxPerPage = 100;

        $sortBy = $request->input('sort_by', 'id');
        if (! in_array($sortBy, $allowedSortColumns, true)) {
            $sortBy = 'id';
        }

        $sortDirection = strtolower($request->input('sort_direction', 'asc'));
        if (! in_array($sortDirection, $allowedSortDirections, true)) {
            $sortDirection = 'asc';
        }

        $perPage = (int) $request->input('per_page', $defaultPerPage);
        $perPage = $perPage > 0 ? min($perPage, $maxPerPage) : $defaultPerPage;

        $query = Role::select(['id', 'name', 'created_at', 'updated_at']);

        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
                if (is_numeric($search)) {
                    $q->orWhere('id', (int) $search);
                }
            });
        }

        $query->orderBy($sortBy, $sortDirection);

        $roles = $query->paginate($perPage)->withQueryString();

        return Inertia::render('maintainers/roles/Index', [
            'roles' => $roles,
            'filters' => $request->only(['search', 'sort_by', 'sort_direction', 'per_page']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $permissions = Permission::select(['id', 'name'])->orderBy('id', 'asc')->get();

        return Inertia::render('maintainers/roles/Create', [
            'permissions' => $permissions,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRoleRequest $request)
    {
        $validated = $request->validated();

        $role = Role::create([
            'name' => $validated['name'],
            'guard_name' => 'web',
        ]);

        if (isset($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        return redirect()
            ->route('maintainers.roles.create')
            ->with('success', 'Role created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Role $role)
    {
        $role->load(['permissions' => function ($query) {
            $query->select(['id', 'name'])->orderBy('id', 'asc');
        }]);

        $allPermissions = Permission::select(['id', 'name'])->orderBy('id', 'asc')->get();

        return Inertia::render('maintainers/roles/Show', [
            'role' => $role,
            'permissions' => $allPermissions,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Role $role)
    {
        $role->load(['permissions' => function ($query) {
            $query->select(['id', 'name'])->orderBy('id', 'asc');
        }]);
        $permissions = Permission::select(['id', 'name'])->orderBy('id', 'asc')->get();

        return Inertia::render('maintainers/roles/Edit', [
            'role' => $role,
            'permissions' => $permissions,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRoleRequest $request, Role $role)
    {
        $validated = $request->validated();

        $role->update([
            'name' => $validated['name'],
        ]);

        if (isset($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        } else {
            $role->syncPermissions([]);
        }

        return redirect()
            ->route('maintainers.roles.edit', $role)
            ->with('success', 'Role updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        $role->delete();

        return back()->with('success', 'Role deleted successfully.');
    }

    /**
     * Export to Excel
     */
    public function export()
    {
        $this->authorize('export', Role::class);
        try {
            $app = config('app.name');
            $tz = config('app.timezone', 'UTC');
            $time = Carbon::now($tz)->format('d-m-Y H-i-s');
            $filename = "{$app} - Roles {$time}.xlsx";

            return Excel::download(new RolesExport, $filename);
        } catch (\Throwable $e) {
            return back()->with('error', 'An error occurred while exporting.');
        }
    }

    /**
     * Show import form
     */
    public function importForm()
    {
        return Inertia::render('maintainers/roles/Import');
    }

    /**
     * Import from Excel
     */
    public function import(Request $request)
    {
        $this->authorize('import', Role::class);
        $request->validate([
            'file' => 'required|file|mimes:xlsx|max:2048',
        ]);

        try {
            Excel::import(new RolesImport, $request->file('file'));

            return redirect()
                ->route('maintainers.roles.import.form')
                ->with('success', 'Roles imported successfully.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Error: '.$e->getMessage());
        }
    }
}
