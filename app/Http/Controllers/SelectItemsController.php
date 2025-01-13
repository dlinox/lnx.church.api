<?php

namespace App\Http\Controllers;

use App\Http\Responses\ApiResponse;
use App\Models\Core\Role;
use App\Models\Minister;
use App\Models\Parish;
use App\Models\SacramentBook;
use Illuminate\Http\Request;

class SelectItemsController extends Controller
{
    public function rolesItems()
    {
        try {
            $items = Role::selectItems();
            return ApiResponse::success($items);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al cargar los roles');
        }
    }

    public function booksItems($type, $search)
    {
        try {
            $items = SacramentBook::selectItems($type, $search);
            return ApiResponse::success($items);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al cargar los libros');
        }
    }

    public function ministersItems()
    {
        try {
            $items = Minister::selectItems();
            return ApiResponse::success($items);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al cargar los ministros');
        }
    }

    public function parishesItems($search)
    {
        try {
            $items = Parish::selectItems($search);
            return ApiResponse::success($items);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al cargar las parroquias');
        }
    }
}
