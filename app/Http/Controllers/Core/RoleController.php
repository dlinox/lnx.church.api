<?php

namespace App\Http\Controllers\Core;

use App\Http\Controllers\Controller;
use App\Http\Requests\Core\RoleRequest;
use App\Http\Responses\ApiResponse;
use App\Models\Core\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role as ModelsRole;

class RoleController extends Controller
{
    protected $role;

    public function __construct(Role $role)
    {
        $this->role = $role;
    }

    public function loadDataTable(Request $request)
    {
        try {
            $items = $this->role
                ->select(
                    'roles.id',
                    'roles.name',
                    'roles.display_name as displayName',
                    DB::raw('(select group_concat(permission_id) from role_has_permissions where role_id = roles.id) as permissions')

                )
                ->where('roles.name', '!=', 'super')
                ->dataTable($request, $this->role::$searchColumns);

            $items->map(function ($item) {
                $item->permissions = array_map('intval', explode(',', $item->permissions));
                return $item;
            });
            return ApiResponse::success($items);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al cargar datos de la tabla');
        }
    }

    public function save(RoleRequest $request)
    {
        try {

            $data = $request->all();

            if ($request->id != null) {
                $item = $this->role->find($request->id);
                $item->update($data);
                return ApiResponse::success(null, 'Rol actualizado con éxito');
            }
            $this->role->create($data);

            return ApiResponse::success(null, 'Rol creado con éxito');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al guardar el rol');
        }
    }

    public function assignPermissions(Request $request)
    {
        try {
            $role = $this->role->find($request->roleId);
            $role->syncPermissions($request->permissions);
            return ApiResponse::success(null, 'Permisos asignados con éxito');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al asignar permisos');
        }
    }

    public function delete($id)
    {
        try {
            ModelsRole::find($id)->delete();
            return ApiResponse::success(null, 'Rol eliminado con éxito');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al eliminar el rol');
        }
    }
}
