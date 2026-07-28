<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Department;
use App\Models\ActivityLog;

class DepartmentController extends Controller
{
    public function index()
    {
        $departments = Department::withCount('users')->orderBy('name')->paginate(15);
        return view('departments.index', compact('departments'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:10|unique:departments',
            'head' => 'nullable|string|max:100',
            'description' => 'nullable|string',
        ]);
        $dept = Department::create($data);
        ActivityLog::log('created', 'department', "Added department: {$dept->name}", $dept);
        return back()->with('success', "Department \"{$dept->name}\" added.");
    }

    public function update(Request $request, Department $department)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'code' => "required|string|max:10|unique:departments,code,{$department->id}",
            'head' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);
        $department->update($data);
        ActivityLog::log('updated', 'department', "Updated department: {$department->name}", $department);
        return back()->with('success', 'Department updated.');
    }

    public function destroy(Department $department)
    {
        if ($department->users()->count() > 0) {
            return back()->with('error', 'Cannot delete department with existing users.');
        }
        $department->delete();
        ActivityLog::log('deleted', 'department', "Deleted department: {$department->name}", $department);
        return back()->with('success', 'Department deleted.');
    }
}
