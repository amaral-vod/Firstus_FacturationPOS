<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class PermissionController extends Controller
{
    public function index()
    {
        $permissions = config('permissions.list');
        $roles = \App\Models\Role::all();

        return view('admin.permissions.index', compact('permissions', 'roles'));
    }
}
