<?php

namespace App\Http\Controllers;

use App\Http\Requests\MinisterRequest;
use App\Http\Responses\ApiResponse;
use App\Models\Minister;
use Illuminate\Http\Request;

class MinisterController extends Controller
{
    protected $minister;

    public function __construct(Minister $minister)
    {
        $this->minister = $minister;
    }

    public function loadDataTable(Request $request)
    {
        try {
            $items = $this->minister
                ->select($this->minister::$dataTableColumns)
                ->dataTable($request);

            return ApiResponse::success($items);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al cargar datos de la tabla');
        }
    }

    public function save(MinisterRequest $request)
    {
        try {
            $data = $request->all();
            if ($request->id != null) {
                $item = $this->minister->find($request->id);
                $item->update($data);
                return ApiResponse::success(null, 'Ministro actualizado con éxito');
            } else {
                $this->minister->create($data);
                return ApiResponse::success(null, 'Ministro creado con éxito');
            }
            return ApiResponse::success($data);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al guardar el ministro');
        }
    }

    public function delete(Request $request)
    {
        try {
            $minister = $this->minister->find($request->id);
            if ($minister->sacraments()->count() > 0) {
                return ApiResponse::error(null, 'No se puede eliminar el ministro porque tiene registros asociados');
            }
            $minister->delete();
            return ApiResponse::success(null, 'Ministro eliminado con éxito');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al eliminar el ministro');
        }
    }
}
