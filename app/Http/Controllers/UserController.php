<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Support\Audit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('roles')->orderBy('name')->get();

        return view('users.index', compact('users'));
    }

    public function create(Request $request)
    {
        abort_unless($request->user()->can('create users'), 403);

        $roles = Role::orderBy('name')->get();

        return view('users.create', compact('roles'));
    }

    public function store(StoreUserRequest $request)
    {
        abort_unless($request->user()->can('create users'), 403);

        $user = User::create($request->validated() + ['password' => Hash::make($request->password)]);
        $user->syncRoles([$request->role]);

        Audit::log('users', "Created user \"{$user->name}\" ({$user->email}) with role {$request->role}", $user, event: 'created');

        return redirect()->route('users.index')->with('status', 'User created.');
    }

    public function edit(Request $request, User $user)
    {
        abort_unless($request->user()->can('edit users'), 403);

        $roles = Role::orderBy('name')->get();

        return view('users.edit', compact('user', 'roles'));
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        abort_unless($request->user()->can('edit users'), 403);

        $data = $request->validated();
        $passwordChanged = ! empty($data['password']);

        if ($passwordChanged) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $role = $data['role'];
        unset($data['role']);

        $before = ['name' => $user->name, 'email' => $user->email, 'role' => $user->getRoleNames()->first()];

        $user->update($data);
        $user->syncRoles([$role]);

        Audit::log('users', "Updated user \"{$user->name}\"", $user, [
            'before' => $before,
            'after' => ['name' => $user->name, 'email' => $user->email, 'role' => $role],
            'password_changed' => $passwordChanged,
        ], event: 'updated');

        return redirect()->route('users.index')->with('status', 'User updated.');
    }

    public function toggleActive(Request $request, User $user)
    {
        abort_unless($request->user()->can('manage users'), 403);
        abort_if($user->is($request->user()), 422, "You can't deactivate your own account.");

        $user->update(['is_active' => ! $user->is_active]);

        Audit::log('users', ($user->is_active ? 'Activated' : 'Deactivated')." user \"{$user->name}\"", $user, event: 'updated');

        return back()->with('status', $user->is_active ? 'User activated.' : 'User deactivated.');
    }

    public function destroy(Request $request, User $user)
    {
        abort_unless($request->user()->can('delete users'), 403);
        abort_if($user->is($request->user()), 422, "You can't delete your own account.");

        if ($user->sales()->exists()) {
            return back()->withErrors(['user' => 'Cannot delete a user with sales history. Deactivate them instead.']);
        }

        Audit::log('users', "Deleted user \"{$user->name}\" ({$user->email})", $user, event: 'deleted');

        $user->delete();

        return redirect()->route('users.index')->with('status', 'User deleted.');
    }
}
