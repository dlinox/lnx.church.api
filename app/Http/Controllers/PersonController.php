<?php

namespace App\Http\Controllers;

use App\Http\Requests\PersonRequest;
use App\Http\Responses\ApiResponse;
use App\Models\Core\Person;
use App\Models\FamilyRelationship;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PersonController extends Controller
{
    protected $person;

    public function __construct(Person $person)
    {
        $this->person = $person;
    }

    public function loadDataTable(Request $request)
    {
        try {
            $items = $this->person
                ->select([
                    ...$this->person::$dataTableColumns,
                    DB::raw('JSON_OBJECT("value", countries.id, "title", countries.name) as birthCountry'),
                    DB::raw('JSON_OBJECT("value", locations.id, "title", CONCAT_WS(", ", locations.department, locations.province, locations.district)) as birthLocation'),
                ])
                ->leftJoin('countries', 'people.birth_country', '=', 'countries.id')
                ->leftJoin('locations', 'people.birth_location', '=', 'locations.id')
                ->orderBy('people.id', 'desc')
                ->dataTable($request);

            return ApiResponse::success($items);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al cargar datos de la tabla');
        }
    }

    public function save(PersonRequest $request)
    {
        try {
            $data = $request->all();

            if ($request->id != null) {
                $person = $this->person->find($request->id);
                $person->update($data);
            } else {
                $person = $this->person->create($data);
            }

            return ApiResponse::success(null, "Persona guardada correctamente");
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al guardar persona');
        }
    }

    public function delete($id)
    {
        try {
            $person = $this->person->find($id);

            if ($person->familyRelationships()->count() > 0 || $person->sacramentRoles()->count() > 0) {
                return ApiResponse::error(null, 'No se puede eliminar la persona porque tiene registros relacionados');
            }
            $person->delete();
            return ApiResponse::success(null, "Persona eliminada correctamente");
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al eliminar persona');
        }
    }

    public function searchSelect($search)
    {
        try {
            $people = $this->person
                ->select(
                    [
                        ...$this->person::$dataTableColumns,
                        DB::raw('JSON_OBJECT("value", countries.id, "title", countries.name) as birthCountry'),
                        DB::raw('JSON_OBJECT("value", locations.id, "title", CONCAT_WS(", ", locations.department, locations.province, locations.district)) as birthLocation'),
                    ]
                )
                ->leftJoin('countries', 'people.birth_country', '=', 'countries.id')
                ->leftJoin('locations', 'people.birth_location', '=', 'locations.id')
                ->where('people.document_number', 'like', '%' . $search . '%')
                ->orWhereRaw("CONCAT(people.name, ' ', people.paternal_last_name, ' ', people.maternal_last_name) like '%$search%'")
                ->limit(20)
                ->get();

            return ApiResponse::success($people);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al buscar personas');
        }
    }

    public function getFamilyRelationships($id)
    {
        try {
            $items = FamilyRelationship::select(
                'family_relationships.related_person_id as personId',
                'family_relationships.relationship as role',
            )
                ->where('family_relationships.person_id', $id)
                ->whereIn('family_relationships.relationship', ['1', '2'])
                ->get();

            return ApiResponse::success($items);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al cargar relaciones familiares');
        }
    }

    public function getPersonById($id)
    {
        try {
            $person = $this->person
                ->select(
                    [
                        ...$this->person::$dataTableColumns,
                        DB::raw('JSON_OBJECT("value", countries.id, "title", countries.name) as birthCountry'),
                        DB::raw('JSON_OBJECT("value", locations.id, "title", CONCAT_WS(", ", locations.department, locations.province, locations.district)) as birthLocation'),
                    ]
                )
                ->leftJoin('countries', 'people.birth_country', '=', 'countries.id')
                ->leftJoin('locations', 'people.birth_location', '=', 'locations.id')
                ->where('people.id', $id)
                ->first();

            return ApiResponse::success($person);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al cargar persona');
        }
    }
}
