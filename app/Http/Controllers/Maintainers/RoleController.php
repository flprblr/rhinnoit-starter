<?php

namespace App\Http\Controllers\Maintainers;

use App\Exports\RolesExport;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Maintainers\Concerns\Searchable;
use App\Http\Requests\Maintainers\Roles\StoreRoleRequest;
use App\Http\Requests\Maintainers\Roles\UpdateRoleRequest;
use App\Imports\RolesImport;
use App\Models\Permission;
use App\Models\Role;
use App\Support\Inertia\PaginatorResource;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class RoleController extends Controller
{
    use Searchable;

    public function __construct()
    {
        $this->authorizeResource(Role::class, 'role');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $perPage = max(1, min(100, $request->integer('per_page', 10)));

        $roles = Role::select(['id', 'name', 'created_at', 'updated_at'])
            ->with('permissions:id,name')
            ->when($this->getCleanSearchTerm($request), function ($query) use ($request) {
                $this->applySearch($query, $request, ['name']);
            })
            ->paginate($perPage)
            ->withQueryString();

        $permissions = Permission::select(['id', 'name'])->orderBy('id', 'asc')->get();

        return Inertia::render('maintainers/roles/Index', [
            'roles' => PaginatorResource::make($roles),
            'permissions' => $permissions,
            'filters' => $request->only(['search']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        $permissions = Permission::select(['id', 'name'])->orderBy('id', 'asc')->get();

        return Inertia::render('maintainers/roles/Create', [
            'permissions' => $permissions,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRoleRequest $request): RedirectResponse
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
            ->route('maintainers.roles.index')
            ->with('success', 'Role created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Role $role): Response
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
    public function edit(Role $role): Response
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
    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
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
            ->route('maintainers.roles.index')
            ->with('success', 'Role updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role): RedirectResponse
    {
        $role->delete();

        return back()->with('success', 'Role deleted successfully.');
    }

    /**
     * Export to Excel
     */
    public function export(): BinaryFileResponse|RedirectResponse
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
    public function importForm(): Response
    {
        return Inertia::render('maintainers/roles/Import');
    }

    /**
     * Import from Excel
     */
    public function import(Request $request): RedirectResponse
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
