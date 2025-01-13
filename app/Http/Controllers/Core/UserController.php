<?php

namespace App\Http\Controllers\Core;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Http\Responses\ApiResponse;
use App\Models\Core\Role;
use App\Models\Core\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    protected $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function loadDataTable(Request $request)
    {
        try {
            $items = $this->user
                ->orderBy('created_at', 'desc')
                ->dataTable($request, ['users.name', 'users.email']);

            $items->map(function ($item) {
                $item->role = Role::select('id as value', 'display_name as title')
                    ->join('model_has_roles', 'roles.id', '=', 'model_has_roles.role_id')
                    ->where('model_has_roles.model_id', $item->id)
                    ->first();
                return $item;
            });

            return ApiResponse::success($items);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al cargar datos de la tabla');
        }
    }

    public function save(UserRequest $request)
    {
        try {
            $data = $request->all();
            DB::beginTransaction();
            if ($request->id == null) {
                $item = $this->user->create($data);
                $item->assignRole($data['role']);
            } else {
                $item = $this->user->find($request->id);
                $item->update([
                    'name' => $data['name'],
                    'username' => $data['username'],
                    'email' => $data['email'],
                    'status' => $data['status'],
                ]);
                $item->syncRoles($data['role']);
            }
            DB::commit();
            return ApiResponse::success($item);
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error($e->getMessage(), 'Error al guardar el usuario');
        }
    }

    public function delete(Request $request)
    {
        try {
            $item = $this->user->find($request->id);
            $role = $item->getRoleNames()->toArray();
            if (in_array('super', $role)) {
                return ApiResponse::error('No autorizado', 'El usuario no puede ser eliminado');
            }
            $item->delete();
            return ApiResponse::success($item);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al eliminar el usuario');
        }
    }
}
