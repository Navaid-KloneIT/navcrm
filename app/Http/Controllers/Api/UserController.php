<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function profile(Request $request): JsonResponse
    {
        return response()->json([
            'user' => new UserResource($request->user()->load('roles', 'permissions')),
        ]);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => ['sometimes', 'nullable', 'string', 'max:50'],
        ]);

        $user->update($validated);

        return response()->json([
            'user' => new UserResource($user->fresh()->load('roles', 'permissions')),
        ]);
    }

    public function updatePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'Current password is incorrect.',
            ], 422);
        }

        $user->update([
            'password' => $request->password,
        ]);

        return response()->json([
            'message' => 'Password updated successfully.',
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $users = User::where('tenant_id', $request->user()->tenant_id)
            ->with('roles')
            ->paginate(15);

        return response()->json(UserResource::collection($users)->response()->getData(true));
    }

    public function show(User $user): JsonResponse
    {
        return response()->json([
            'user' => new UserResource($user->load('roles', 'permissions')),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'phone' => ['nullable', 'string', 'max:50'],
            'is_active' => ['nullable', 'boolean'],
            'role_ids' => ['nullable', 'array'],
            'role_ids.*' => ['exists:roles,id'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'phone' => $validated['phone'] ?? null,
            'tenant_id' => $request->user()->tenant_id,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        if (!empty($validated['role_ids'])) {
            $roles = Role::whereIn('id', $validated['role_ids'])->where('guard_name', 'web')->get();
            $user->syncRoles($roles);
        }

        return response()->json([
            'user' => new UserResource($user->load('roles', 'permissions')),
        ], 201);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['sometimes', 'string', 'min:8'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:50'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $user->update($validated);

        return response()->json([
            'user' => new UserResource($user->fresh()->load('roles', 'permissions')),
        ]);
    }

    public function destroy(User $user): JsonResponse
    {
        $user->delete();

        return response()->json(null, 204);
    }

    public function syncRoles(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'role_ids' => ['required', 'array'],
            'role_ids.*' => ['exists:roles,id'],
        ]);

        $roles = Role::whereIn('id', $validated['role_ids'])->where('guard_name', 'web')->get();
        $user->syncRoles($roles);

        return response()->json([
            'user' => new UserResource($user->fresh()->load('roles', 'permissions')),
        ]);
    }
}
