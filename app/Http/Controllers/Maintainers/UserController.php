<?php

namespace App\Http\Controllers\Maintainers;

use App\Exports\UsersExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Maintainers\Users\StoreUserRequest;
use App\Http\Requests\Maintainers\Users\UpdateUserRequest;
use App\Imports\UsersImport;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class UserController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(User::class, 'user');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $allowedSortColumns = ['id', 'name', 'email', 'created_at', 'updated_at'];
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

        $query = User::select(['id', 'name', 'email', 'created_at', 'updated_at']);

        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
                if (is_numeric($search)) {
                    $q->orWhere('id', (int) $search);
                }
            });
        }

        $query->orderBy($sortBy, $sortDirection);

        $users = $query->paginate($perPage)->withQueryString();

        return Inertia::render('maintainers/users/Index', [
            'users' => $users,
            'filters' => $request->only(['search', 'sort_by', 'sort_direction', 'per_page']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $roles = Role::select(['id', 'name'])->orderBy('id', 'asc')->get();

        return Inertia::render('maintainers/users/Create', [
            'roles' => $roles,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request)
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        if (isset($validated['roles'])) {
            $user->syncRoles($validated['roles']);
        }

        return redirect()
            ->route('maintainers.users.create')
            ->with('success', 'User created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        $user->load([
            'roles' => function ($query) {
                $query->select(['id', 'name'])->orderBy('id', 'asc');
            },
            'roles.permissions' => function ($query) {
                $query->select(['id', 'name'])->orderBy('id', 'asc');
            },
        ]);

        $allRoles = Role::select(['id', 'name'])->orderBy('id', 'asc')->get();

        return Inertia::render('maintainers/users/Show', [
            'user' => $user,
            'roles' => $allRoles,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $user->load([
            'roles' => function ($query) {
                $query->select(['id', 'name'])->orderBy('id', 'asc');
            },
            'roles.permissions' => function ($query) {
                $query->select(['id', 'name'])->orderBy('id', 'asc');
            },
        ]);
        $roles = Role::select(['id', 'name'])->orderBy('id', 'asc')->get();

        return Inertia::render('maintainers/users/Edit', [
            'user' => $user,
            'roles' => $roles,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $validated = $request->validated();

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($validated['password']);
        }

        $user->update($data);

        if (isset($validated['roles'])) {
            $user->syncRoles($validated['roles']);
        } else {
            $user->syncRoles([]);
        }

        return redirect()
            ->route('maintainers.users.edit', $user)
            ->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $user->delete();

        return back()->with('success', 'User deleted successfully.');
    }

    /**
     * Export to Excel
     */
    public function export()
    {
        $this->authorize('export', User::class);
        try {
            $app = config('app.name');
            $tz = config('app.timezone', 'UTC');
            $time = Carbon::now($tz)->format('d-m-Y H-i-s');
            $filename = "{$app} - Users {$time}.xlsx";

            return Excel::download(new UsersExport, $filename);
        } catch (\Throwable $e) {
            return back()->with('error', 'An error occurred while exporting.');
        }
    }

    /**
     * Show import form
     */
    public function importForm()
    {
        return Inertia::render('maintainers/users/Import');
    }

    /**
     * Import from Excel
     */
    public function import(Request $request)
    {
        $this->authorize('import', User::class);
        $request->validate([
            'file' => 'required|file|mimes:xlsx|max:2048',
        ]);

        try {
            Excel::import(new UsersImport, $request->file('file'));

            return redirect()
                ->route('maintainers.users.import.form')
                ->with('success', 'Users imported successfully.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Error: '.$e->getMessage());
        }
    }
}
