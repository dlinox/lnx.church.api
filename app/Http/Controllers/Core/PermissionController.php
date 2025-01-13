<?php

namespace App\Http\Controllers\Core;

use App\Http\Controllers\Controller;
use App\Http\Requests\Core\PermissionRequest;
use App\Http\Responses\ApiResponse;
use App\Models\Core\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PermissionController extends Controller
{
    protected $permission;

    public function __construct(Permission $permission)
    {
        $this->permission = $permission;
    }

    public function loadDataTable(Request $request)
    {
        try {
            $items = $this->permission
                ->select(
                    'permissions.id',
                    'permissions.name',
                    'permissions.display_name as displayName',
                    'permissions.type',
                    'permissions.parent_id as parentId',
                    'parent.display_name as parentName'
                )
                ->join('permissions as parent', 'permissions.parent_id', '=', 'parent.id')
                ->dataTable($request, $this->permission::$searchColumns);

            return ApiResponse::success($items);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al cargar datos de la tabla');
        }
    }


    public function save(PermissionRequest $request)
    {
        try {
            DB::beginTransaction();
            if ($request->id != null) {
                $item = $this->permission->find($request->id);
                $item->update($request->all());

                DB::commit();
                return ApiResponse::success(null, 'Permiso actualizado con éxito');
            }
            $permission = $this->permission->create($request->all());

            if ($request->type == '001') {
                $this->permission->create([
                    'display_name' => 'Menu ' . $permission->display_name,
                    'name' => Str::snake('Menu ' . $permission->display_name),
                    'type' => '002',
                    'parent_id' => $permission->id
                ]);
            }

            DB::commit();
            return ApiResponse::success(null, 'Permiso creado con éxito');
        } catch (\Exception $e) {

            DB::rollBack();
            return ApiResponse::error($e->getMessage(), 'Error al crear o actualizar permiso');
        }
    }

    public function allPermissions()
    {
        try {
            $permissions = $this->permission->select('id', 'display_name as name', 'parent_id as parentId')
                ->get();

            $parents = $permissions->where('parentId', null);

            $permissions = $parents->map(function ($parent) use ($permissions) {
                $children = $permissions->where('parentId', $parent->id)->values();
                $parent->children = $children;
                return $parent;
            })->values();

            return ApiResponse::success($permissions->toArray());
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al cargar los permisos');
        }
    }
}
