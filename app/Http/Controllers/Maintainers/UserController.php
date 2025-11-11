<?php

namespace App\Http\Controllers\Maintainers;

use App\Exports\UsersExport;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Maintainers\Concerns\Searchable;
use App\Http\Requests\Maintainers\Users\StoreUserRequest;
use App\Http\Requests\Maintainers\Users\UpdateUserRequest;
use App\Imports\UsersImport;
use App\Models\Role;
use App\Models\User;
use App\Support\Inertia\PaginatorResource;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class UserController extends Controller
{
    use Searchable;

    public function __construct()
    {
        $this->authorizeResource(User::class, 'user');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $perPage = max(1, min(100, $request->integer('per_page', 10)));

        $users = User::select(['id', 'name', 'email', 'created_at', 'updated_at'])
            ->with('roles:id,name')
            ->when($this->getCleanSearchTerm($request), function ($query) use ($request) {
                $this->applySearch($query, $request, ['name', 'email']);
            })
            ->paginate($perPage)
            ->withQueryString();

        $roles = Role::select(['id', 'name'])->orderBy('id', 'asc')->get();

        return Inertia::render('maintainers/users/Index', [
            'users' => PaginatorResource::make($users),
            'roles' => $roles,
            'filters' => $request->only(['search']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        $roles = Role::select(['id', 'name'])->orderBy('id', 'asc')->get();

        return Inertia::render('maintainers/users/Create', [
            'roles' => $roles,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request): RedirectResponse
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
            ->route('maintainers.users.index')
            ->with('success', 'User created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user): Response
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
    public function edit(User $user): Response
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
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
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
            ->route('maintainers.users.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user): RedirectResponse
    {
        $user->delete();

        return back()->with('success', 'User deleted successfully.');
    }

    /**
     * Export to Excel
     */
    public function export(): BinaryFileResponse|RedirectResponse
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
    public function importForm(): Response
    {
        return Inertia::render('maintainers/users/Import');
    }

    /**
     * Import from Excel
     */
    public function import(Request $request): RedirectResponse
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
