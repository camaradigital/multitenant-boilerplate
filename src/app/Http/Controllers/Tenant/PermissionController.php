<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Permission; // Usando o model personalizado
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;

class PermissionController extends Controller
{
    /**
     * Aplica a policy aos métodos do resource controller.
     */
    public function __construct()
    {
        $this->authorizeResource(Permission::class, 'permission');
    }

    public function index()
    {
        // A autorização 'viewAny' é aplicada automaticamente pelo __construct

        $permissions = Permission::where('guard_name', 'tenant')
            ->latest()
            ->get();

        return inertia('Tenant/Permissions/Index', [
            'permissions' => $permissions,
        ]);
    }

    public function store(Request $request)
    {
        // A autorização 'create' é aplicada automaticamente pelo __construct

        $validated = $request->validate([
            'name' => 'required|string|max:125|unique:tenant.permissions,name,NULL,id,guard_name,tenant',
        ]);

        Permission::create([
            'name' => $validated['name'],
            'guard_name' => 'tenant',
        ]);

        return Redirect::route('admin.permissions.index')->with('success', 'Permissão criada com sucesso.');
    }

    public function update(Request $request, Permission $permission)
    {
        // A autorização 'update' é aplicada automaticamente pelo __construct

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:125', Rule::unique('tenant.permissions', 'name')->where('guard_name', 'tenant')->ignore($permission->id)],
        ]);

        $permission->update($validated);

        return Redirect::route('admin.permissions.index')->with('success', 'Permissão atualizada com sucesso.');
    }

    public function destroy(Permission $permission)
    {
        // A autorização 'delete' é aplicada automaticamente pelo __construct

        // Proteção: não permite excluir uma permissão que está sendo usada por algum papel.
        if ($permission->roles()->count() > 0) {
            return Redirect::back()->with('error', 'Não é possível excluir. Esta permissão está em uso por um ou mais papéis.');
        }

        $permission->delete();

        return Redirect::route('admin.permissions.index')->with('success', 'Permissão excluída com sucesso.');
    }
}
