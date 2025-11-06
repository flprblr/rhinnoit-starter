<?php

namespace App\Http\Controllers\Maintainers;

use App\Exports\PermissionsExport;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Maintainers\Concerns\Searchable;
use App\Http\Requests\Maintainers\Permissions\StorePermissionRequest;
use App\Http\Requests\Maintainers\Permissions\UpdatePermissionRequest;
use App\Imports\PermissionsImport;
use App\Models\Permission;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PermissionController extends Controller
{
    use Searchable;

    public function __construct()
    {
        $this->authorizeResource(Permission::class, 'permission');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $permissions = Permission::select(['id', 'name', 'created_at', 'updated_at'])
            ->when($this->getCleanSearchTerm($request), function ($query) use ($request) {
                $this->applySearch($query, $request, ['name']);
            })
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('maintainers/permissions/Index', [
            'permissions' => $permissions,
            'filters' => $request->only(['search']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('maintainers/permissions/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePermissionRequest $request): RedirectResponse
    {
        Permission::create([
            'name' => $request->validated()['name'],
            'guard_name' => 'web',
        ]);

        return redirect()
            ->route('maintainers.permissions.index')
            ->with('success', 'Permission created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Permission $permission): Response
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
    public function edit(Permission $permission): Response
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
    public function update(UpdatePermissionRequest $request, Permission $permission): RedirectResponse
    {
        $permission->update([
            'name' => $request->validated()['name'],
        ]);

        return redirect()
            ->route('maintainers.permissions.index')
            ->with('success', 'Permission updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Permission $permission): RedirectResponse
    {
        $permission->delete();

        return back()->with('success', 'Permission deleted successfully.');
    }

    /**
     * Export to Excel
     */
    public function export(): BinaryFileResponse|RedirectResponse
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
    public function importForm(): Response
    {
        return Inertia::render('maintainers/permissions/Import');
    }

    /**
     * Import from Excel
     */
    public function import(Request $request): RedirectResponse
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
