<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ActivityLog;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::withCount('users')->get();
        return view('roles.index', compact('roles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|unique:roles,name']);
        $role = Role::create(['name' => $data['name'], 'guard_name' => 'web']);
        ActivityLog::log('created', 'role', "Created role: {$role->name}");
        return back()->with('success', "Role \"{$role->name}\" created.");
    }

    public function destroy(int $id)
    {
        $role = Role::findOrFail($id);
        $systemRoles = [
            'admin', 'supply-officer', 'staff', 'auditor', 'budget-officer', 'accounting', 
            'regional-director', 'assistant-regional-director', 'client',
            'supply-staff', 'budget-staff', 'accounting-staff', 'ard-staff', 'rd-staff'
        ];
        if (in_array($role->name, $systemRoles)) {
            return back()->with('error', 'Cannot delete a system role.');
        }
        $role->delete();
        ActivityLog::log('deleted', 'role', "Deleted role: {$role->name}");
        return back()->with('success', 'Role deleted.');
    }
}
