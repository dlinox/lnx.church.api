<?php

namespace App\Http\Controllers;

use App\Http\Responses\ApiResponse;
use App\Models\Parish;
use Illuminate\Http\Request;

class ParishController extends Controller
{
    protected $parish;

    public function __construct(Parish $parish)
    {
        $this->parish = $parish;
    }
    public function loadDataTable(Request $request)
    {
        try {
            $items = $this->parish
                ->select($this->parish::$dataTableColumns)
                ->dataTable($request);

            return ApiResponse::success($items);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al cargar datos de la tabla');
        }
    }

    public function save(Request $request)
    {
        try {
            $data = $request->all();
            if ($request->id != null) {
                $item = $this->parish->find($request->id);
                $item->update($data);
                return ApiResponse::success(null, 'Parroquia actualizada con éxito');
            } else {
                $this->parish->create($data);
                return ApiResponse::success(null, 'Parroquia creada con éxito');
            }
            return ApiResponse::success($data);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al guardar la parroquia');
        }
    }

    //delete but where has sacraments
    public function delete(Request $request)
    {
        try {
            $parish = $this->parish->find($request->id);
            if ($parish->sacraments()->count() > 0) {
                return ApiResponse::error(null, 'No se puede eliminar la parroquia porque tiene sacramentos asociados');
            }
            $parish->delete();
            return ApiResponse::success(null, 'Parroquia eliminada con éxito');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al eliminar la parroquia');
        }
    }
}
