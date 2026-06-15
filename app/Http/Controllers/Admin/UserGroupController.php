<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UserGroupController extends Controller
{
    public function index()
    {
        $groups = UserGroup::withCount('users')->get();
        $allPermissions = config('permissions.list');

        return view('admin.groups.index', compact('groups', 'allPermissions'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'permissions' => 'nullable|array',
        ]);
        $data['slug'] = Str::slug($data['name']);
        UserGroup::create($data);

        return back()->with('success', '✅ Groupe créé.');
    }

    public function update(Request $request, UserGroup $group)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'permissions' => 'nullable|array',
        ]);
        $data['slug'] = Str::slug($data['name']);
        $group->update($data);

        return back()->with('success', '✅ Groupe mis à jour.');
    }

    public function destroy(UserGroup $group)
    {
        $group->delete();

        return back()->with('success', '🗑️ Groupe supprimé.');
    }
}
