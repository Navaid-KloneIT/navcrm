<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionController extends Controller
{
    private function formatRole(Role $role): array
    {
        return [
            'id' => $role->id,
            'name' => $role->name,
            'guard_name' => $role->guard_name,
            'permissions' => $role->permissions->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name,
            ])->values(),
            'created_at' => $role->created_at?->toIso8601String(),
            'updated_at' => $role->updated_at?->toIso8601String(),
        ];
    }

    public function index(Request $request): JsonResponse
    {
        $roles = Role::where('guard_name', 'web')->with('permissions')->get();

        return response()->json([
            'data' => $roles->map(fn ($role) => $this->formatRole($role)),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name'],
            'permission_ids' => ['nullable', 'array'],
            'permission_ids.*' => ['exists:permissions,id'],
        ]);

        $role = Role::create([
            'name' => $validated['name'],
            'guard_name' => 'web',
        ]);

        if (!empty($validated['permission_ids'])) {
            $permissions = Permission::whereIn('id', $validated['permission_ids'])->where('guard_name', 'web')->get();
            $role->syncPermissions($permissions);
        }

        $role->load('permissions');

        return response()->json([
            'data' => $this->formatRole($role),
        ], 201);
    }

    public function show(Role $role): JsonResponse
    {
        $role->load('permissions');

        return response()->json([
            'data' => $this->formatRole($role),
        ]);
    }

    public function update(Request $request, Role $role): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255', 'unique:roles,name,' . $role->id],
            'permission_ids' => ['nullable', 'array'],
            'permission_ids.*' => ['exists:permissions,id'],
        ]);

        if (isset($validated['name'])) {
            $role->update(['name' => $validated['name']]);
        }

        if (isset($validated['permission_ids'])) {
            $permissions = Permission::whereIn('id', $validated['permission_ids'])->where('guard_name', 'web')->get();
            $role->syncPermissions($permissions);
        }

        $role = $role->fresh()->load('permissions');

        return response()->json([
            'data' => $this->formatRole($role),
        ]);
    }

    public function destroy(Role $role): JsonResponse
    {
        $protectedRoles = ['admin', 'manager', 'sales', 'viewer'];

        if (in_array($role->name, $protectedRoles)) {
            return response()->json([
                'message' => 'This role cannot be deleted.',
            ], 403);
        }

        $role->delete();

        return response()->json(null, 204);
    }

    public function permissions(Request $request): JsonResponse
    {
        $permissions = Permission::where('guard_name', 'web')->get();

        return response()->json([
            'data' => $permissions->map(function ($permission) {
                return [
                    'id' => $permission->id,
                    'name' => $permission->name,
                    'guard_name' => $permission->guard_name,
                ];
            }),
        ]);
    }
}
