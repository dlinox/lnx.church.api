<?php

namespace App\Http\Controllers;

use App\Http\Responses\ApiResponse;
use App\Models\Sacrament;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SacramentController extends Controller
{
    protected $sacrament;

    public function __construct(Sacrament $sacrament)
    {
        $this->sacrament = $sacrament;
    }

    public function loadDataTable(Request $request, $type)
    {
        try {
            $items = $this->sacrament
                ->select($this->sacrament::$dataTableColumns)
                ->byType($type)
                ->with(['minister', 'parish:id,name'])
                ->where('sacraments.is_external', 0)
                ->orderBy('date', 'desc')
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
            if ($request->id == null) {
                $item = $this->sacrament->create([
                    'date' => $data['date'],
                    'description' => $data['description'],
                    'type' => $data['type'],
                    'is_external' => false,
                    'parish_id' => 1,
                    'minister_id' => $data['ministerId'],
                ]);
            } else {
                $item = $this->sacrament->find($request->id);
                $item->update([
                    'date' => $data['date'],
                    'description' => $data['description'],
                    'minister_id' => $data['ministerId'],
                ]);
            }
            return ApiResponse::success($item);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al guardar el registro');
        }
    }

    public function getSacramentById($id)
    {
        try {
            $item = $this->sacrament->select([
                ...$this->sacrament::$dataTableColumns,
                'parishes.name as parish',
                DB::raw("CONCAT_WS(' ', ministers.name, ministers.paternal_last_name, ministers.maternal_last_name) as minister"),
            ])
                ->join('ministers', 'sacraments.minister_id', '=', 'ministers.id')
                ->join('parishes', 'sacraments.parish_id', '=', 'parishes.id')
                ->where('sacraments.id', $id)
                ->first();
            return ApiResponse::success($item);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al obtener el registro');
        }
    }

    public function delete($id)
    {
        try {
            $sacrament = $this->sacrament->find($id);
            if ($sacrament->sacramentRecords()->count() > 0) {
                return ApiResponse::error('', 'No se puede eliminar el registro porque tiene registros asociados');
            }

            $sacrament->delete();
            return ApiResponse::success(null, "Registro eliminado");
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al eliminar el registro');
        }
    }

    
}
