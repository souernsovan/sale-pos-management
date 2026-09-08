<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Support\Audit;
use Database\Seeders\RolesAndPermissionsSeeder;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::withCount('users')->with('permissions')->orderBy('name')->get();
        $modules = RolesAndPermissionsSeeder::MODULES;
        $builtIn = RolesAndPermissionsSeeder::BUILT_IN_ROLES;

        $matrix = [];
        foreach ($roles as $role) {
            $rolePermissions = $role->permissions->pluck('name')->all();
            foreach ($modules as $moduleName => $moduleActions) {
                $matrix[$role->id][$moduleName] = $this->badgeFor($moduleActions, $rolePermissions);
            }
        }

        return view('roles.index', compact('roles', 'modules', 'builtIn', 'matrix'));
    }

    public function create()
    {
        $modules = RolesAndPermissionsSeeder::MODULES;
        $actions = RolesAndPermissionsSeeder::ACTIONS;
        $rolePermissions = [];

        return view('roles.create', compact('modules', 'actions', 'rolePermissions'));
    }

    public function store(StoreRoleRequest $request)
    {
        $role = Role::create(['name' => $request->name]);
        $role->syncPermissions($request->input('permissions', []));

        Audit::log('roles', "Created role \"{$role->name}\"", $role, [
            'permissions' => $request->input('permissions', []),
        ], event: 'created');

        return redirect()->route('roles.index')->with('status', 'Role created.');
    }

    public function edit(Role $role)
    {
        abort_if($role->name === 'Super Admin', 422, 'Super Admin permissions cannot be edited.');

        $modules = RolesAndPermissionsSeeder::MODULES;
        $actions = RolesAndPermissionsSeeder::ACTIONS;
        $rolePermissions = $role->permissions->pluck('name')->all();
        $hasDefault = RolesAndPermissionsSeeder::defaultPermissionsFor($role->name) !== null;

        return view('roles.edit', compact('role', 'modules', 'actions', 'rolePermissions', 'hasDefault'));
    }

    public function update(UpdateRoleRequest $request, Role $role)
    {
        abort_if($role->name === 'Super Admin', 422, 'Super Admin permissions cannot be edited.');

        $before = ['name' => $role->name, 'permissions' => $role->permissions->pluck('name')->all()];

        $role->update(['name' => $request->name]);
        $role->syncPermissions($request->input('permissions', []));

        Audit::log('roles', "Updated role \"{$role->name}\"", $role, [
            'before' => $before,
            'after' => ['name' => $role->name, 'permissions' => $request->input('permissions', [])],
        ], event: 'updated');

        return redirect()->route('roles.index')->with('status', 'Role updated.');
    }

    public function resetDefault(Role $role)
    {
        $defaults = RolesAndPermissionsSeeder::defaultPermissionsFor($role->name);

        abort_if($defaults === null, 422, 'This role has no default permission set to reset to.');

        $role->syncPermissions($defaults);

        Audit::log('roles', "Reset role \"{$role->name}\" to its default permissions", $role, [
            'permissions' => $defaults,
        ], event: 'updated');

        return redirect()->route('roles.edit', $role)->with('status', 'Role reset to its default permissions.');
    }

    public function destroy(Role $role)
    {
        abort_if($role->name === 'Super Admin', 422, "Super Admin can't be deleted.");

        if ($role->users()->exists()) {
            return back()->withErrors(['role' => 'Cannot delete a role that still has users assigned.']);
        }

        Audit::log('roles', "Deleted role \"{$role->name}\"", $role, event: 'deleted');

        $role->delete();

        return redirect()->route('roles.index')->with('status', 'Role deleted.');
    }

    /**
     * @param  array<string, string>  $moduleActions  action => permission string, for this module
     * @param  string[]  $rolePermissions  every permission the role currently has
     * @return array{label: string, level: string}
     */
    private function badgeFor(array $moduleActions, array $rolePermissions): array
    {
        $definedPermissions = array_values($moduleActions);
        $has = array_intersect($definedPermissions, $rolePermissions);

        if (empty($has)) {
            return ['label' => '—', 'level' => 'none'];
        }

        if (count($has) === count($definedPermissions)) {
            return ['label' => 'Full', 'level' => 'full'];
        }

        foreach (['manage', 'create', 'edit', 'delete'] as $writeAction) {
            if (isset($moduleActions[$writeAction]) && in_array($moduleActions[$writeAction], $has, true)) {
                return ['label' => 'Manage', 'level' => 'manage'];
            }
        }

        return ['label' => 'View', 'level' => 'view'];
    }
}
