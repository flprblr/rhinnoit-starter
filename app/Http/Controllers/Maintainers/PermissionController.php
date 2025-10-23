<?php

namespace App\Http\Controllers\Maintainers;

use App\Exports\PermissionsExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Maintainers\Permissions\StorePermissionRequest;
use App\Http\Requests\Maintainers\Permissions\UpdatePermissionRequest;
use App\Imports\PermissionsImport;
use App\Models\Permission;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class PermissionController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Permission::class, 'permission');
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

        $query = Permission::select(['id', 'name', 'created_at', 'updated_at']);

        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
                if (is_numeric($search)) {
                    $q->orWhere('id', (int) $search);
                }
            });
        }

        $query->orderBy($sortBy, $sortDirection);

        $permissions = $query->paginate($perPage)->withQueryString();

        return Inertia::render('maintainers/permissions/Index', [
            'permissions' => $permissions,
            'filters' => $request->only(['search', 'sort_by', 'sort_direction', 'per_page']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('maintainers/permissions/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePermissionRequest $request)
    {
        Permission::create([
            'name' => $request->validated()['name'],
            'guard_name' => 'web',
        ]);

        return redirect()
            ->route('maintainers.permissions.create')
            ->with('success', 'Permission created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Permission $permission)
    {
        $permission->load(['roles' => function ($query) {
            $query->select(['id', 'name'])->orderBy('id', 'asc');
        }]);

        return Inertia::render('maintainers/permissions/Show', [
            'permission' => $permission,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Permission $permission)
    {
        $permission->load(['roles' => function ($query) {
            $query->select(['id', 'name'])->orderBy('id', 'asc');
        }]);

        return Inertia::render('maintainers/permissions/Edit', [
            'permission' => $permission,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePermissionRequest $request, Permission $permission)
    {
        $permission->update([
            'name' => $request->validated()['name'],
        ]);

        return redirect()
            ->route('maintainers.permissions.edit', $permission)
            ->with('success', 'Permission updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Permission $permission)
    {
        $permission->delete();

        return back()->with('success', 'Permission deleted successfully.');
    }

    /**
     * Export to Excel
     */
    public function export()
    {
        $this->authorize('export', Permission::class);
        try {
            $app = config('app.name');
            $tz = config('app.timezone', 'UTC');
            $time = Carbon::now($tz)->format('d-m-Y H-i-s');
            $filename = "{$app} - Permissions {$time}.xlsx";

            return Excel::download(new PermissionsExport, $filename);
        } catch (\Throwable $e) {
            return back()->with('error', 'An error occurred while exporting.');
        }
    }

    /**
     * Show import form
     */
    public function importForm()
    {
        return Inertia::render('maintainers/permissions/Import');
    }

    /**
     * Import from Excel
     */
    public function import(Request $request)
    {
        $this->authorize('import', Permission::class);
        $request->validate([
            'file' => 'required|file|mimes:xlsx|max:2048',
        ]);

        try {
            Excel::import(new PermissionsImport, $request->file('file'));

            return redirect()
                ->route('maintainers.permissions.import.form')
                ->with('success', 'Permissions imported successfully.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Error: '.$e->getMessage());
        }
    }
}
