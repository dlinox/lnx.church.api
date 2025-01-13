<?php

namespace App\Http\Controllers;

use App\Http\Requests\SacramentBookRequest;
use App\Http\Responses\ApiResponse;
use App\Models\SacramentBook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SacramentBookController extends Controller
{

    protected $sacramentBooks;

    public function __construct(SacramentBook $sacramentBooks)
    {
        $this->sacramentBooks = $sacramentBooks;
    }

    public function loadDataTable(Request $request)
    {
        try {
            $items = $this->sacramentBooks
                ->select(
                    'sacrament_books.id',
                    'sacrament_books.number',
                    'sacrament_books.folios_number as foliosNumber',
                    'sacrament_books.year_start as yearStart',
                    'sacrament_books.year_finish as yearFinish',
                    'sacrament_books.acts_per_folio as actsPerFolio',
                    'sacrament_books.sacrament_type as sacramentType'
                )
                ->orderBy('sacrament_books.number', 'desc')
                ->dataTable($request, $this->sacramentBooks::$searchColumns);

            return ApiResponse::success($items);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al cargar datos de la tabla');
        }
    }

    public function save(SacramentBookRequest $request)
    {
        try {
            if ($request->id != null) {
                $item = $this->sacramentBooks->find($request->id);
                $item->update($request->all());
                return ApiResponse::success(null, 'Libro sacramental actualizado con éxito');
            }
            $this->sacramentBooks->create($request->all());

            return ApiResponse::success(null, 'Libro sacramental creado con éxito');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al guardar el libro sacramental');
        }
    }

    public function delete($id)
    {
        try {
            $item = $this->sacramentBooks->find($id);
            if ($item->sacramentRecords->count() > 0) {
                return ApiResponse::error(null, 'No se puede eliminar el libro sacramental porque tiene registros asociados');
            }
            $item->delete();
            return ApiResponse::success(null, 'Libro sacramental eliminado con éxito');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al eliminar el libro sacramental');
        }
    }

    //obtener la numeración del libro, folio y acta
    public function getBookNumbering($id)
    {
        try {
            $book = $this->sacramentBooks
                ->select(
                    'sacrament_records.folio_number as folioNumber',
                    // 'sacrament_books.acts_per_folio as maxActNumberPerFolio',
                    DB::raw('MAX(sacrament_books.acts_per_folio) as maxActNumberPerFolio'),
                    DB::raw('MAX(sacrament_records.act_number) as actNumber'),
                    DB::raw('COUNT(sacrament_records.act_number) as cantActInFolio')
                )
                ->join('sacrament_records', 'sacrament_books.id', '=', 'sacrament_records.sacrament_book_id')
                ->where('sacrament_books.id', $id)
                ->groupBy('sacrament_records.folio_number')
                ->orderByDesc('sacrament_records.folio_number')
                ->first();

            $data = [];

            if ($book) {
                $data['last']['folioNumber'] = $book->folioNumber;
                $data['last']['actNumber'] = $book->actNumber;

                if ($book->cantActInFolio >= $book->maxActNumberPerFolio) {
                    $data['next']['folioNumber'] = $book->folioNumber + 1;
                    $data['next']['actNumber'] = $book->actNumber + 1;
                } else {
                    $data['next']['folioNumber'] = $book->folioNumber;
                    $data['next']['actNumber'] = $book->actNumber + 1;
                }
            } else {
                $data['last']['folioNumber'] = null;
                $data['last']['actNumber'] = null;

                $data['next']['folioNumber'] = 1;
                $data['next']['actNumber'] = 1;
            }

            return ApiResponse::success($data);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al obtener el número de libro');
        }
    }
}
