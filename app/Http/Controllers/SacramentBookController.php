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

    //book registers
    public function getBookRegisters($id)
    {
        try {
            // $registers = [];
            $sacramentBooks = $this->sacramentBooks->find($id);
            if (!$sacramentBooks) {
                return ApiResponse::error(null, 'No se encontró el libro sacramental');
            }

            if ($sacramentBooks->sacrament_type['value'] == '1' || $sacramentBooks->sacrament_type['value'] == '2') {
                $registers = $this->sacramentBooks
                    ->select(
                        'people.document_type as documentType',
                        'people.document_number as documentNumber',
                        'people.name',
                        'people.paternal_last_name as paternalLastName',
                        'people.maternal_last_name as maternalLastName',
                        'sacrament_records.folio_number as folioNumber',
                        'sacrament_records.act_number as actNumber',
                        'sacrament_records.observation',
                        'sacrament_records.status'
                    )
                    ->join('sacrament_records', 'sacrament_books.id', '=', 'sacrament_records.sacrament_book_id')
                    ->join('sacrament_roles', 'sacrament_records.id', '=', 'sacrament_roles.sacrament_record_id')
                    ->join('people', 'sacrament_roles.person_id', '=', 'people.id')
                    ->where('sacrament_roles.role', '1')
                    ->where('sacrament_books.id', $id)
                    ->where('sacrament_books.sacrament_type', $sacramentBooks->sacrament_type)
                    ->orderBy('sacrament_records.folio_number', 'asc')
                    ->orderBy('sacrament_records.act_number', 'asc')
                    ->get();
            }

            if ($sacramentBooks->sacrament_type['value'] == '4') {
                $registers = $this->sacramentBooks
                    ->select(
                        DB::raw("GROUP_CONCAT(DISTINCT IF(husband.name IS NOT NULL, CONCAT_WS(' ', husband.name, husband.paternal_last_name, husband.maternal_last_name), NULL)) AS 'husband'"),
                        DB::raw("GROUP_CONCAT(DISTINCT IF(wife.name IS NOT NULL, CONCAT_WS(' ', wife.name, wife.paternal_last_name, wife.maternal_last_name), NULL)) AS 'wife'"),
                        'sacrament_records.folio_number as folioNumber',
                        'sacrament_records.act_number as actNumber',
                        'sacrament_records.observation',
                        'sacrament_records.status'
                    )
                    ->join('sacrament_records', 'sacrament_books.id', '=', 'sacrament_records.sacrament_book_id')
                    ->join('sacrament_roles', 'sacrament_records.id', '=', 'sacrament_roles.sacrament_record_id')
                    ->leftJoin('people as husband', function ($join) {
                        $join->on('husband.id', '=', 'sacrament_roles.person_id')
                            ->where('sacrament_roles.role', '=', '2');
                    })
                    ->leftJoin('people as wife', function ($join) {
                        $join->on('wife.id', '=', 'sacrament_roles.person_id')
                            ->where('sacrament_roles.role', '=', '3');
                    })->whereIn('sacrament_roles.role', ['2', '3'])
                    ->where('sacrament_books.id', $id)
                    ->where('sacrament_books.sacrament_type', $sacramentBooks->sacrament_type)
                    ->orderBy('sacrament_records.folio_number', 'asc')
                    ->orderBy('sacrament_records.act_number', 'asc')
                    ->groupBy('sacrament_records.id')
                    ->get();
            }

            return ApiResponse::success($registers);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al obtener los registros del libro');
        }
    }
}
