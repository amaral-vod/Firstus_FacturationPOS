<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::withCount('users')->get();

        return view('admin.roles.index', compact('roles'));
    }

    public function edit(Role $role)
    {
        $allPermissions = config('permissions.list');

        return view('admin.roles.edit', compact('role', 'allPermissions'));
    }

    public function update(Request $request, Role $role)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string',
        ]);

        $data['slug'] = Str::slug($data['name']);
        $role->update($data);

        ActivityLogger::log('modification', 'roles', "Modification rôle {$role->name}");

        return redirect()->route('admin.roles.index')->with('success', '✅ Rôle mis à jour.');
    }
}
