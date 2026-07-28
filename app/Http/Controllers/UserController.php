<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\Department;
use App\Models\ActivityLog;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with(['department', 'roles']);
        if ($search = $request->search) {
            $query->where('name', 'like', "%$search%")->orWhere('email', 'like', "%$search%");
        }
        if ($role = $request->role) {
            $query->role($role);
        }
        $users       = $query->orderBy('name')->paginate(15)->withQueryString();
        $roles       = Role::all();
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        return view('users.index', compact('users', 'roles', 'departments'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:100',
            'email'         => 'required|email|unique:users',
            'password'      => 'required|string|min:8|confirmed',
            'department_id' => 'nullable|exists:departments,id',
            'position'      => 'nullable|string|max:100',
            'phone'         => 'nullable|string|max:20',
            'role'          => 'required|exists:roles,name',
        ]);

        $user = User::create([
            'name'          => $data['name'],
            'email'         => $data['email'],
            'password'      => Hash::make($data['password']),
            'department_id' => $data['department_id'] ?? null,
            'position'      => $data['position'] ?? null,
            'phone'         => $data['phone'] ?? null,
            'is_active'     => true,
        ]);
        $user->syncRoles([$data['role']]);

        ActivityLog::log('created', 'user', "Created user: {$user->name}", $user);
        return redirect()->route('users.index')->with('success', "User \"{$user->name}\" created.");
    }

    public function edit(User $user)
    {
        $roles       = Role::all();
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        return view('users.edit', compact('user', 'roles', 'departments'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:100',
            'email'         => "required|email|unique:users,email,{$user->id}",
            'department_id' => 'nullable|exists:departments,id',
            'position'      => 'nullable|string|max:100',
            'phone'         => 'nullable|string|max:20',
            'role'          => 'required|exists:roles,name',
        ]);

        $user->update([
            'name'          => $data['name'],
            'email'         => $data['email'],
            'department_id' => $data['department_id'] ?? null,
            'position'      => $data['position'] ?? null,
            'phone'         => $data['phone'] ?? null,
        ]);
        $user->syncRoles([$data['role']]);

        ActivityLog::log('updated', 'user', "Updated user: {$user->name}", $user);
        return redirect()->route('users.index')->with('success', 'User updated.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Cannot delete your own account.');
        }
        $user->delete();
        ActivityLog::log('deleted', 'user', "Deleted user: {$user->name}", $user);
        return redirect()->route('users.index')->with('success', 'User deleted.');
    }

    public function toggleStatus(int $id)
    {
        $user = User::findOrFail($id);
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Cannot deactivate your own account.');
        }
        $user->update(['is_active' => !$user->is_active]);
        $status = $user->is_active ? 'activated' : 'deactivated';
        ActivityLog::log('toggled', 'user', "User {$status}: {$user->name}", $user);
        return back()->with('success', "User {$status}.");
    }

    public function resetPassword(Request $request, int $id)
    {
        $data = $request->validate(['password' => 'required|string|min:8|confirmed']);
        $user = User::findOrFail($id);
        $user->update(['password' => Hash::make($data['password'])]);
        ActivityLog::log('password_reset', 'user', "Password reset for: {$user->name}", $user);
        return back()->with('success', 'Password reset successfully.');
    }

    public function profile()
    {
        $user        = auth()->user();
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        return view('users.profile', compact('user', 'departments'));
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();
        $data = $request->validate([
            'name'     => 'required|string|max:100',
            'phone'    => 'nullable|string|max:20',
            'position' => 'nullable|string|max:100',
            'avatar'   => 'nullable|image',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        if ($request->hasFile('avatar') && $request->file('avatar')->isValid()) {
            if ($user->avatar && !str_starts_with($user->avatar, 'data:')) {
                Storage::disk('public')->delete($user->avatar);
            }
            $file = $request->file('avatar');
            $file->store('avatars', 'public');
            $mime = $file->getClientMimeType() ?: $file->getMimeType();
            $base64 = base64_encode($file->get());
            $data['avatar'] = 'data:' . $mime . ';base64,' . $base64;
        }

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);
        ActivityLog::log('updated', 'profile', 'Profile updated', $user);
        return back()->with('success', 'Profile updated successfully.');
    }
}
