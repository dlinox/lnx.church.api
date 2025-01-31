<?php

namespace App\Http\Controllers;

use App\Http\Responses\ApiResponse;
use App\Models\Core\Country;
use App\Models\Core\Location;
use App\Models\Core\Person;
use App\Models\FamilyRelationship;
use App\Models\Minister;
use App\Models\Parish;
use App\Models\PrintingRecord;
use App\Models\Sacrament;
use App\Models\SacramentBook;
use App\Models\SacramentRecord;
use App\Models\SacramentRole;
use Barryvdh\DomPDF\PDF;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SacramentRecordController extends Controller
{
    protected $sacramentRecord;
    protected $sacramentRole;

    public function __construct(SacramentRecord $sacramentRecord)
    {
        $this->sacramentRecord = $sacramentRecord;
        $this->sacramentRole =  new SacramentRole();
    }

    public function loadDataTable(Request $request, $sacramentId)
    {
        try {
            $items = $this->sacramentRecord
                ->select(
                    [
                        ...$this->sacramentRecord::$dataTableColumns,
                        'sacrament_books.number as bookNumber',
                        DB::raw("GROUP_CONCAT(DISTINCT IF(fellow.name IS NOT NULL, CONCAT_WS(' ', fellow.name, fellow.paternal_last_name, fellow.maternal_last_name), NULL)) AS 'fellow'"),
                        DB::raw("GROUP_CONCAT(DISTINCT IF(fellowParents.name IS NOT NULL, CONCAT_WS(' ', fellowParents.name, fellowParents.paternal_last_name, fellowParents.maternal_last_name), NULL) SEPARATOR ', ') AS 'fellowParents'"),
                        DB::raw("GROUP_CONCAT(DISTINCT IF(husband.name IS NOT NULL, CONCAT_WS(' ', husband.name, husband.paternal_last_name, husband.maternal_last_name), NULL)) AS 'husband'"),
                        DB::raw("GROUP_CONCAT(DISTINCT IF(husbandParents.name IS NOT NULL, CONCAT_WS(' ', husbandParents.name, husbandParents.paternal_last_name, husbandParents.maternal_last_name), NULL) SEPARATOR ', ') AS 'husbandParents'"),
                        DB::raw("GROUP_CONCAT(DISTINCT IF(wife.name IS NOT NULL, CONCAT_WS(' ', wife.name, wife.paternal_last_name, wife.maternal_last_name), NULL)) AS 'wife'"),
                        DB::raw("GROUP_CONCAT(DISTINCT IF(wifeParents.name IS NOT NULL, CONCAT_WS(' ', wifeParents.name, wifeParents.paternal_last_name, wifeParents.maternal_last_name), NULL) SEPARATOR ', ') AS 'wifeParents'"),
                        DB::raw("GROUP_CONCAT(DISTINCT IF(godparents.name IS NOT NULL, CONCAT_WS(' ', godparents.name, godparents.paternal_last_name, godparents.maternal_last_name), NULL) SEPARATOR ', ') AS 'godparents'")
                    ]
                )
                ->join('sacrament_roles', 'sacrament_records.id', '=', 'sacrament_roles.sacrament_record_id')
                ->leftJoin('people as fellow', function ($join) {
                    $join->on('fellow.id', '=', 'sacrament_roles.person_id')
                        ->where('sacrament_roles.role', '=', '1');
                })
                ->leftJoin('people as husband', function ($join) {
                    $join->on('husband.id', '=', 'sacrament_roles.person_id')
                        ->where('sacrament_roles.role', '=', '2');
                })
                ->leftJoin('people as wife', function ($join) {
                    $join->on('wife.id', '=', 'sacrament_roles.person_id')
                        ->where('sacrament_roles.role', '=', '3');
                })
                ->leftJoin('people as godparents', function ($join) {
                    $join->on('godparents.id', '=', 'sacrament_roles.person_id')
                        ->whereIn('sacrament_roles.role', ['4', '5']);
                })
                ->leftJoin('family_relationships as fellowFamily', function ($join) {
                    $join->on('sacrament_roles.person_id', '=', 'fellowFamily.person_id')
                        ->whereIn('sacrament_roles.role', ['1']);
                })
                ->leftJoin('people as fellowParents', 'fellowParents.id', '=', 'fellowFamily.related_person_id')
                ->leftJoin('family_relationships as husbandFamily', function ($join) {
                    $join->on('sacrament_roles.person_id', '=', 'husbandFamily.person_id')
                        ->whereIn('sacrament_roles.role', ['2']);
                })
                ->leftJoin('people as husbandParents', 'husbandParents.id', '=', 'husbandFamily.related_person_id')
                ->leftJoin('family_relationships as wifeFamily', function ($join) {
                    $join->on('sacrament_roles.person_id', '=', 'wifeFamily.person_id')
                        ->whereIn('sacrament_roles.role', ['3']);
                })
                ->leftJoin('people as wifeParents', 'wifeParents.id', '=', 'wifeFamily.related_person_id')
                ->leftJoin('sacrament_books', 'sacrament_books.id', '=', 'sacrament_records.sacrament_book_id')
                ->where('sacrament_records.sacrament_id', $sacramentId)
                ->groupBy('sacrament_records.id')
                ->orderBy('sacrament_records.id', 'desc')
                ->dataTable($request);

            return ApiResponse::success($items);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al cargar datos de la tabla');
        }
    }

    public function save(Request $request)
    {
        $user = Auth::user();
        $data = $request->all();

        $id = $request->id ? $request->id : null;

        $sacrament = Sacrament::find($request->sacramentId);
        if (!$sacrament) {
            return ApiResponse::error(null, 'El sacramento no existe');
        } else {
            if ($sacrament->type == 1 || $sacrament->type == 2) {
                $roles = collect($request->roles);
                $fellow  = $roles->where('role', 1)
                    ->where('personId', '!=', null)
                    ->first();
                $role4 = $roles->where('role', 4)
                    ->where('personId', '!=', null)
                    ->first();
                $role5 = $roles->where('role', 5)
                    ->where('personId', '!=', null)
                    ->first();

                if (!$fellow) {
                    return ApiResponse::error(null, 'Se debe asignar al bautizado / confirmado');
                } else {
                    if ($data['issued'] && $sacrament->type == "2") {
                        $hasBaptism = $this->existBaptismPerson($fellow['personId']);
                        if (!$hasBaptism) {
                            return ApiResponse::error(null, 'No se puede asentar la confirmación sin el bautismo');
                        }
                    }
                }
                if (!$role4 && !$role5) {
                    return ApiResponse::error(null, 'Se debe asignar padrino y/o madrina');
                }
            }
        }
        if ($request->sacramentBookId) {
            $sacramentBook = SacramentBook::find($request->sacramentBookId['value']);
            if (!$sacramentBook) {
                return ApiResponse::error(null, 'El libro de sacramentos no existe');
            }
            $sacramentRecord = SacramentRecord::where('folio_number', $request->folioNumber)
                ->where('act_number', $request->actNumber)
                ->where('sacrament_id', $request->sacramentId)
                ->where('sacrament_book_id', $request->sacramentBookId)
                ->where('id', '!=', $id)
                ->exists();
            if ($sacramentRecord) {
                return ApiResponse::error(null, 'La Información del acta ya existe');
            }
            $data['sacramentBookId'] = $request->sacramentBookId['value'];
        } else {
            $data['folioNumber'] = null;
            $data['actNumber'] = null;
            $data['issued'] = false;
        }

        try {
            DB::beginTransaction();
            if ($id) {
                $sacramentRecord = SacramentRecord::find($id);
                $sacramentRecord->folio_number = $data['folioNumber'];
                $sacramentRecord->act_number =  $data['actNumber'];
                $sacramentRecord->observation = $data['observations'];
                $sacramentRecord->issue_date = $data['issued']  ? now() : null;
                $sacramentRecord->sacrament_book_id = $data['sacramentBookId'];
                $sacramentRecord->sacrament_id = $data['sacramentId'];
                $sacramentRecord->canonical = $data['canonical'];
                $sacramentRecord->user_id = $user->id;
                $sacramentRecord->save();

                $sacramentRoles = SacramentRole::where('sacrament_record_id', $id)->get();
                foreach ($sacramentRoles as $role) {
                    $role->delete();
                }
            } else {
                $sacramentRecord = new SacramentRecord();
                $sacramentRecord->folio_number = $data['folioNumber'];
                $sacramentRecord->act_number =  $data['actNumber'];
                $sacramentRecord->observation = $data['observations'];
                $sacramentRecord->issue_date = $data['issued']  ? now() : null;
                $sacramentRecord->sacrament_book_id = $data['sacramentBookId'];
                $sacramentRecord->sacrament_id = $data['sacramentId'];
                $sacramentRecord->canonical = $data['canonical'];
                $sacramentRecord->user_id = $user->id;
                $sacramentRecord->save();
            }


            foreach ($request->roles as $role) {
                if ($role['personId'] != null) {

                    $sacramentRole = new SacramentRole();
                    $sacramentRole->role = $role['role'];
                    $sacramentRole->person_id = $role['personId'];
                    $sacramentRole->sacrament_record_id = $sacramentRecord->id;
                    $sacramentRole->save();

                    if (in_array($role['role'], [1, 2, 3])) {

                        //eliminamos las relaciones familiares
                        FamilyRelationship::whereIN('relationship', ['1', '2'])
                            ->where('person_id', $role['personId'])->delete();

                        foreach ($role['family'] as $family) {
                            if ($family['personId'] == null) continue;
                            $familyRelationship = FamilyRelationship::where('person_id', $role['personId'])
                                ->where('relationship', $family['role'])
                                ->first();
                            if ($familyRelationship) {
                                $familyRelationship->related_person_id = $family['personId'];
                                $familyRelationship->save();
                            } else {
                                $familyRelationshipNew = new FamilyRelationship();
                                $familyRelationshipNew->relationship = $family['role'];
                                $familyRelationshipNew->person_id = $role['personId'];
                                $familyRelationshipNew->related_person_id = $family['personId'];
                                $familyRelationshipNew->save();
                            }
                        }
                    }
                }
            }

            DB::commit();
            return ApiResponse::success(null, 'Registro guardado correctamente');
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error($e->getMessage(), 'Error al guardar el registro');
        }
    }

    public function saveExternalBaptism(Request $request)
    {
        $user = Auth::user();
        $data = $request->all();

        try {

            DB::beginTransaction();
            $sacrament = Sacrament::create([
                'date' => $data['date'],
                'description' => null,
                'type' => $data['type'],
                'is_external' => true,
                'parish_id' => $data['parish']['value'],
                'minister_id' => null,
            ]);

            $sacramentRecord = new SacramentRecord();
            $sacramentRecord->sacrament_id = $sacrament->id;
            $sacramentRecord->user_id = $user->id;
            $sacramentRecord->save();

            $role = new SacramentRole();
            $role->role = 1;
            $role->person_id = $data['personId'];
            $role->sacrament_record_id = $sacramentRecord->id;
            $role->save();

            DB::commit();
            return ApiResponse::success(null, 'Registro guardado correctamente');
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error($e->getMessage(), 'Error al guardar el registro');
        }
    }

    public function getRecordById($id)
    {
        try {
            $sacramentRecord = SacramentRecord::find($id);
            if (!$sacramentRecord) {
                return ApiResponse::error(null, 'El registro no existe');
            }
            $sacramentRoles = SacramentRole::where('sacrament_record_id', $id)->get();

            $sacramentBook = SacramentBook::find($sacramentRecord->sacrament_book_id);

            $record = [];


            $record['id'] = $sacramentRecord->id;
            $record['sacramentId'] = $sacramentRecord->sacrament_id;

            if ($sacramentBook) {
                $sacramentBookYearFinish = $sacramentBook->year_finish ? $sacramentBook->year_finish : 'Actualidad';
                $record['sacramentBookId'] = [
                    'value' => $sacramentBook->id,
                    'title' => $sacramentBook->number . ' (' . $sacramentBook->year_start . ' - ' . $sacramentBookYearFinish  . ')'
                ];
            } else {
                $record['sacramentBookId'] = null;
            }


            $record['folioNumber'] = $sacramentRecord->folio_number;
            $record['actNumber'] = $sacramentRecord->act_number;
            $record['observations'] = $sacramentRecord->observation;
            $record['canonical'] = $sacramentRecord->canonical;
            $record['issued'] = $sacramentRecord->status;

            $roles = [];

            foreach ($sacramentRoles as $role) {
                $roles[] = [
                    'role' => $role->role,
                    'personId' => $role->person_id,
                ];
                if (in_array($role->role, ['1', '2', '3'])) {
                    $family = FamilyRelationship::where('person_id', $role->person_id)->get();
                    $familyArray = [];
                    foreach ($family as $f) {
                        $familyArray[] = [
                            'role' => $f->relationship,
                            'personId' => $f->related_person_id
                        ];
                    }
                    $roles[count($roles) - 1]['family'] = $familyArray;
                }
            }

            $record['roles'] = $roles;

            return ApiResponse::success($record);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al obtener el registro');
        }
    }

    public function invalidate(Request $request)
    {
        try {
            $sacramentRecord = SacramentRecord::find($request->id);
            if ($sacramentRecord) {
                $sacramentRecord->observation =  trim($request->observation);
                $sacramentRecord->status = false;
                $sacramentRecord->save();
                return ApiResponse::success(null, 'Registro anulado correctamente');
            } else {
                return ApiResponse::error(null, 'El registro no existe');
            }
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al anular el registro');
        }
    }

    public function delete($id)
    {
        try {
            $sacramentRecord = SacramentRecord::find($id);
            if ($sacramentRecord) {
                $sacramentRoles = SacramentRole::where('sacrament_record_id', $id)->get();
                foreach ($sacramentRoles as $role) {
                    $role->delete();
                }
                $sacramentRecord->delete();
                return ApiResponse::success(null, 'Registro eliminado correctamente');
            } else {
                return ApiResponse::error(null, 'El registro no existe');
            }
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al eliminar el registro');
        }
    }

    public function getPrintData(Request $request)
    {

        $data = $this->printData($request->id);

        if ($data->sacramentType == '1') {
            $background = config('app.url') . '/templates/baptism.jpeg';
            $url  = $this->generateCertificate($data->toArray(), $background, 'pdf.certificates.baptism')['url'];
        } else if ($data->sacramentType == '2') {
            $background = config('app.url') . '/templates/confirmation.jpeg';
            $url  = $this->generateCertificate($data->toArray(), $background, 'pdf.certificates.confirmation')['url'];
        } else if ($data->sacramentType == '4') {
            $background = config('app.url') . '/templates/marriage.jpeg';
            $url  = $this->generateCertificate($data->toArray(), $background, 'pdf.certificates.marriage')['url'];
        }

        $response = [
            'urlTemp' => $url,
            'data' => $data,
            'existPrintings' => PrintingRecord::existsPrintingRecord($request->id)
        ];

        return ApiResponse::success($response);
    }

    public function printRecord(Request $request)
    {
        try {
            $user = Auth::user();
            $data = $request->all();

            DB::beginTransaction();

            $sacrament = SacramentRecord::join('sacraments', 'sacrament_records.sacrament_id', '=', 'sacraments.id')
                ->where('sacrament_records.id', $data['id'])
                ->first();

            if ($data['observations'] != null || $data['observations'] != '') {
                SacramentRecord::where('id', $data['id'])->update(['observation' => $data['observations']]);
            }

            PrintingRecord::register($data, $user->id);

            if ($sacrament->type == 1) {
                $pdf = $this->generateCertificate($data, null, 'pdf.certificates.baptism')['pdf'];
            } else if ($sacrament->type == 2) {
                $pdf = $this->generateCertificate($data, null, 'pdf.certificates.confirmation')['pdf'];
            } else if ($sacrament->type == 4) {
                $pdf = $this->generateCertificate($data, null, 'pdf.certificates.marriage')['pdf'];
            }

            DB::commit();
            return response($pdf->output())
                ->header('Content-Type', 'application/pdf');
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error($e->getMessage(), 'Error al imprimir el registro');
        }
    }


    private function generateCertificate($data, $background, $view)
    {
        $pdf = app(PDF::class);

        $pdf->setOptions([
            'margin-top' => 0,
            'margin-right' => 0,
            'margin-bottom' => 0,
            'margin-left' => 0,
            'isRemoteEnabled' => true
        ]);

        $pdf = $pdf->loadView($view, [
            ...$data,
            'background' => $background
        ]);

        $fileName = 'temp_pdf_' . uniqid() . '.pdf';

        $filePath = 'temp/' . $fileName;
        Storage::put($filePath, $pdf->output());

        $url = Storage::temporaryUrl($filePath, now()->addMinutes(15));

        return [
            'url' => $url,
            'pdf' => $pdf
        ];
    }

    public function getSacramentsPerson($personId)
    {

        try {
            $data = [
                'baptism' => null,
                'confirmation' => null
            ];

            $baptism = SacramentRecord::select(
                'sacraments.date as date',
                'parishes.name as parish',
            )
                ->join('sacraments', 'sacrament_records.sacrament_id', '=', 'sacraments.id')
                ->join('sacrament_roles', 'sacrament_records.id', '=', 'sacrament_roles.sacrament_record_id')
                ->join('parishes', 'sacraments.parish_id', '=', 'parishes.id')
                ->where('sacrament_roles.person_id', $personId)
                ->where('sacraments.type', 1)
                ->where('sacrament_roles.role', 1)
                ->first();

            if ($baptism) {
                $baptism->date = Carbon::parse($baptism->date)->locale('es')->isoFormat('D [de] MMMM, YYYY');
                $data['baptism'] = $baptism;
            }

            $confirmation = SacramentRecord::select(
                'sacraments.date as date',
                'parishes.name as parish',
            )
                ->join('sacraments', 'sacrament_records.sacrament_id', '=', 'sacraments.id')
                ->join('sacrament_roles', 'sacrament_records.id', '=', 'sacrament_roles.sacrament_record_id')
                ->join('parishes', 'sacraments.parish_id', '=', 'parishes.id')
                ->where('sacrament_roles.person_id', $personId)
                ->where('sacraments.type', 2)
                ->where('sacrament_roles.role', 1)
                ->first();

            if ($confirmation) {
                $confirmation->date = Carbon::parse($confirmation->date)->locale('es')->isoFormat('D [de] MMMM, YYYY');
                $data['confirmation'] = $confirmation;
            }

            return ApiResponse::success($data);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al obtener el registro');
        }
    }

    private function existBaptismPerson($personId)
    {
        $sacramentRecord = SacramentRecord::join('sacraments', 'sacrament_records.sacrament_id', '=', 'sacraments.id')
            ->join('sacrament_roles', 'sacrament_records.id', '=', 'sacrament_roles.sacrament_record_id')
            ->where('sacrament_roles.person_id', $personId)
            ->where('sacrament_roles.role', 1)
            ->where('sacraments.type', 1)
            ->exists();

        return $sacramentRecord;
    }

    public function searchActs($search)
    {
        $items = $this->sacramentRole::select(
            DB::raw("CONCAT_WS(' ', people.document_number, people.name, people.paternal_last_name, people.maternal_last_name) as person"),
            'sacraments.type',
            'sacraments.date',
            'sacrament_records.id',
        )
            ->join('sacrament_records', 'sacrament_records.id', '=', 'sacrament_roles.sacrament_record_id')
            ->join('sacraments', 'sacraments.id', 'sacrament_records.sacrament_id')
            ->join('people', 'people.id', 'sacrament_roles.person_id')
            ->where('sacrament_records.status', 1)
            ->where('sacrament_records.issue_date', '!=', null)
            ->where('sacraments.is_external', 0)
            ->whereIn('sacrament_roles.role', ['1', '2', '3'])
            ->whereRaw("CONCAT_WS(' ', people.document_number, people.name, people.paternal_last_name, people.maternal_last_name) LIKE ?", ["%{$search}%"])
            ->limit(30)
            ->get()
            ->map(function ($item) {
                $item->date = Carbon::parse($item->date)->locale('es')->isoFormat('D [de] MMMM, YYYY');
                return $item;
            });

        if (count($items) > 0) {
            return ApiResponse::success($items);
        }
        return ApiResponse::success([], 'No se encontro resultados');
    }

    public function reportCountRecordByType()
    {
        try {
            $items = $this->sacramentRecord
                ->select([
                    'sacraments.type',
                    DB::raw('count(*) as count'),
                ])
                ->join('sacraments', 'sacrament_records.sacrament_id', '=', 'sacraments.id')
                ->where('sacraments.is_external', 0)
                ->where('sacrament_records.status', 1)
                ->groupBy('sacraments.type')
                ->get();
            return ApiResponse::success($items);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al obtener los datos');
        }
    }

    public function getDataEdit($id)
    {
        try {

            $sacramentRecord = SacramentRecord::find($id);
            if (!$sacramentRecord) {
                return ApiResponse::error(null, 'El registro no existe');
            }

            $sacramentBook = SacramentBook::find($sacramentRecord->sacrament_book_id);

            $sacrament = Sacrament::find($sacramentRecord->sacrament_id);

            $record = [];

            $record['id'] = $sacramentRecord->id;
            $record['sacramentId'] = $sacramentRecord->sacrament_id;

            if ($sacramentBook) {
                $sacramentBookYearFinish = $sacramentBook->year_finish ? $sacramentBook->year_finish : 'Actualidad';
                $record['sacramentBook'] = [
                    'value' => $sacramentBook->id,
                    'title' => $sacramentBook->number . ' (' . $sacramentBook->year_start . ' - ' . $sacramentBookYearFinish  . ')'
                ];
            } else {
                $record['sacramentBook'] = null;
            }

            $record['folioNumber'] = $sacramentRecord->folio_number;
            $record['actNumber'] = $sacramentRecord->act_number;
            $record['observations'] = $sacramentRecord->observation;
            $record['canonical'] = $sacramentRecord->canonical;
            $record['issued'] = $sacramentRecord->status;
            $record['date']  = $sacrament->date;
            $record['type'] = $sacrament->type;

            $record['parish'] = Parish::select(
                'parishes.id as value',
                'parishes.name as title',
            )
                ->where('parishes.id', $sacrament->parish_id)
                ->first();

            $record['minister'] = Minister::select(
                'ministers.id as value',
                DB::raw("CONCAT_WS(' ', ministers.name, ministers.paternal_last_name, ministers.maternal_last_name) as title"),
            )
                ->where('ministers.id', $sacrament->minister_id)
                ->first();


            $roles = $this->getRoles($id, $sacrament->type);

            $record['roles'] = $roles;

            return ApiResponse::success($record);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Error al obtener los datos');
        }
    }

    public function getRoles($recordId, $type)
    {

        $roles = [];

        if (in_array($type, ['1', '2'])) {

            $roles['fellow'] = $this->getPersonWithFamilyDetails($recordId, '1');
            if ($type == '2') {
                $roles['fellow']->baptism = $this->getSacramentDetails($roles['fellow']->personId, 1, '1');
            }
        }

        if ($type == '4') {
            $roles['husband'] = $this->getPersonWithFamilyDetails($recordId, '2');
            $roles['husband']->baptism = $this->getSacramentDetails($roles['husband']->personId, 1, '1');
            $roles['husband']->confirmation = $this->getSacramentDetails($roles['husband']->personId, 2, '1');

            $roles['wife'] = $this->getPersonWithFamilyDetails($recordId, '3');
            $roles['wife']->baptism = $this->getSacramentDetails($roles['wife']->personId, 1, '1');
            $roles['wife']->confirmation = $this->getSacramentDetails($roles['wife']->personId, 2, '1');

            $roles['witnesses'] = $this->getWitnesses($recordId);
        }

        $roles['godparents'] = $this->getGodparents($recordId);

        return $roles;
    }


    private function getPersonDetails($recordId, $role)
    {
        $person = SacramentRole::select(
            'sacrament_roles.person_id as personId',
            'sacrament_roles.role',
            'people.document_type as documentType',
            'people.document_number as documentNumber',
            'people.name as name',
            'people.paternal_last_name as paternalLastName',
            'people.maternal_last_name as maternalLastName',
            'people.birth_date as birthDate',
            'people.gender',
            'people.birth_country as birthCountry',
            'people.birth_location as birthLocation'
        )
            ->join('people', 'sacrament_roles.person_id', '=', 'people.id')
            ->where('sacrament_roles.role', $role)
            ->where('sacrament_roles.sacrament_record_id', $recordId)
            ->first();

        $person->birthCountry = $this->getCountryDetails($person->birthCountry);
        $person->birthLocation = $this->getLocationDetails($person->birthLocation);

        return $person;
    }

    private function getCountryDetails($countryId)
    {
        return Country::select('countries.id as value', 'countries.name as title')
            ->where('countries.id', $countryId)
            ->first();
    }

    private function getLocationDetails($locationId)
    {
        return Location::select(
            'locations.id as value',
            DB::raw("CONCAT_WS(', ', locations.department, locations.province, locations.district) as title")
        )
            ->where('locations.id', $locationId)
            ->first();
    }

    private function getSacramentDetails($personId, $type, $role)
    {
        return SacramentRecord::select(
            'sacraments.date as date',
            'parishes.name as parish',
            'sacraments.is_external as isExternal',
        )
            ->join('sacraments', 'sacrament_records.sacrament_id', '=', 'sacraments.id')
            ->join('sacrament_roles', 'sacrament_records.id', '=', 'sacrament_roles.sacrament_record_id')
            ->join('parishes', 'sacraments.parish_id', '=', 'parishes.id')
            ->where('sacrament_roles.person_id', $personId)
            ->where('sacraments.type', $type)
            ->where('sacrament_roles.role', $role)
            ->first();
    }

    private function getPersonWithFamilyDetails($recordId, $role)
    {
        $person = $this->getPersonDetails($recordId, $role);

        $person->father = $this->getFamilyMember($person->personId, '1');
        $person->mother = $this->getFamilyMember($person->personId, '2');

        return $person;
    }

    private function getFamilyMember($personId, $relationship)
    {
        return FamilyRelationship::select(
            'family_relationships.related_person_id as personId',
            'family_relationships.relationship as role',
            'people.name as name',
            'people.paternal_last_name as paternalLastName',
            'people.maternal_last_name as maternalLastName',
            'people.gender'
        )
            ->join('people', 'family_relationships.related_person_id', '=', 'people.id')
            ->where('family_relationships.person_id', $personId)
            ->where('family_relationships.relationship', $relationship)
            ->first();
    }

    private function getWitnesses($recordId)
    {
        return SacramentRole::select(
            'sacrament_roles.person_id as personId',
            'sacrament_roles.role',
            'people.name as name',
            'people.paternal_last_name as paternalLastName',
            'people.maternal_last_name as maternalLastName',
            'people.birth_date as birthDate',
            'people.gender'
        )
            ->join('people', 'sacrament_roles.person_id', '=', 'people.id')
            ->whereIN('sacrament_roles.role', ['6'])
            ->where('sacrament_roles.sacrament_record_id', $recordId)
            ->orderBy('sacrament_roles.role')
            ->get();
    }

    private function getGodparents($recordId)
    {
        return SacramentRole::select(
            'sacrament_roles.person_id as personId',
            'sacrament_roles.role',
            'people.name as name',
            'people.paternal_last_name as paternalLastName',
            'people.maternal_last_name as maternalLastName',
            'people.birth_date as birthDate',
            'people.gender'
        )
            ->join('people', 'sacrament_roles.person_id', '=', 'people.id')
            ->whereIN('sacrament_roles.role', ['4', '5'])
            ->where('sacrament_roles.sacrament_record_id', $recordId)
            ->orderBy('sacrament_roles.role')
            ->get();
    }


    public function updateAct(Request $request)
    {

        $sacramentRecord = SacramentRecord::find($request->id);
        if (!$sacramentRecord) {
            return ApiResponse::error(null, 'El registro no existe');
        }

        $data = $request->all();

        try {
            DB::beginTransaction();

            $sacrament = Sacrament::find($sacramentRecord->sacrament_id);

            if ($sacrament->parish_id != $data['parish']['value'] || $sacrament->minister_id != $data['minister']['value'] || $sacrament->date != $data['date']) {
                $sacrament = Sacrament::create([
                    'date' => $data['date'],
                    'description' => null,
                    'type' => $sacrament->type,
                    'is_external' => false,
                    'parish_id' => $data['parish']['value'],
                    'minister_id' => $data['minister']['value'],
                ]);
            }

            $record = SacramentRecord::where('folio_number', $data['folioNumber'])
                ->where('act_number', $data['actNumber'])
                ->where('sacrament_book_id', $data['sacramentBook']['value'])
                ->where('id', '!=', $sacramentRecord->id)
                ->exists();

            if ($record) {
                throw new \Exception('Ya existe un registro con el mismo número de folio y acta');
            }

            $sacramentRecord->sacrament_book_id = $data['sacramentBook']['value'];
            $sacramentRecord->folio_number = $data['folioNumber'];
            $sacramentRecord->act_number = $data['actNumber'];
            $sacramentRecord->observation = $data['observations'];
            $sacramentRecord->canonical = $data['canonical'];
            $sacramentRecord->sacrament_id = $sacrament->id;
            $sacramentRecord->save();

            $roles = $data['roles'];
            if ($sacrament->type == '1' || $sacrament->type == '2') {
                $this->updateMainPerson($roles['fellow']);
            }


            if ($sacrament->type == '4') {
                $this->updateMainPerson($roles['husband']);
                $this->updateMainPerson($roles['wife']);
                $witnesses = $roles['witnesses'];

                foreach ($witnesses as $witness) {
                    $this->updateSecondaryPerson($witness);
                }
            }

            $godparents = $roles['godparents'];

            foreach ($godparents as $godparent) {
                $this->updateSecondaryPerson($godparent);
            }

            DB::commit();
            return ApiResponse::success(null, 'Registro actualizado correctamente');
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error($e->getMessage(), 'Error al actualizar el registro: ' . $e->getMessage());
        }
    }

    private function updateMainPerson($data)
    {

        //el tipo de documento  y el numero de documento puden ser nulos, enses caso no se valida

        if ($data['documentType'] && $data['documentNumber']) {
            $person = Person::where('document_type', $data['documentType'])
                ->where('document_number', $data['documentNumber'])
                ->where('id', '!=', $data['personId'])
                ->exists();

            if ($person) {
                throw new \Exception('Ya existe otra persona con el documento: ' . $data['documentNumber']);
            }
        }


        $person = Person::find($data['personId']);

        // var_dump($data);

        $person->document_type = $data['documentType'];
        $person->document_number = $data['documentNumber'];
        $person->name = $data['name'];
        $person->paternal_last_name = $data['paternalLastName'];
        $person->maternal_last_name = $data['maternalLastName'];
        $person->birth_date = $data['birthDate'];
        $person->gender = $data['gender'];

        $person->birth_country = $data['birthCountry'] ? $data['birthCountry'] : null;
        $person->birth_location = $data['birthLocation']  ? $data['birthLocation'] : null;
        $person->save();

        // var_dump($person);

        //actualizar familia
        $father = $data['father'];
        $this->updateSecondaryPerson($father);

        $mother = $data['mother'];
        $this->updateSecondaryPerson($mother);

        return $person->id;
    }

    private function updateSecondaryPerson($data)
    {
        try {

            $person = Person::find($data['personId']);
            $person->name = $data['name'];
            $person->paternal_last_name = $data['paternalLastName'];
            $person->maternal_last_name = $data['maternalLastName'];
            $person->gender = $data['gender'];
            $person->save();
            return $person->id;
        } catch (\Exception $e) {
            throw new \Exception('Error al actualizar la persona');
        }
    }

    private function printData($id)
    {
        $data = $this->sacramentRecord
            ->select(
                'sacrament_records.id as id',
                'parishes.name as parish',
                DB::raw("CONCAT_WS(' ', ministers.name, ministers.paternal_last_name, ministers.maternal_last_name) as minister"),
                'sacrament_books.number as bookNumber',
                'sacrament_records.folio_number as folioNumber',
                'sacrament_records.act_number as actNumber',
                'sacrament_records.issue_date as issueDate',
                'sacrament_records.observation as observations',
                'sacraments.date as sacramentDate',
                'sacraments.type as sacramentType',
            )
            ->join('sacraments', 'sacrament_records.sacrament_id', '=', 'sacraments.id')
            ->join('ministers', 'sacraments.minister_id', '=', 'ministers.id')
            ->join('parishes', 'sacraments.parish_id', '=', 'parishes.id')
            ->join('sacrament_books', 'sacrament_records.sacrament_book_id', '=', 'sacrament_books.id')
            ->where('sacrament_records.id', $id)
            ->first();

        if (!$data) {

            throw new \Exception('El registro no existe');
        }

        $fellows = SacramentRole::select(
            'sacrament_roles.person_id as personId',
            'sacrament_roles.role',
            'people.name as name',
            'people.birth_date as birthDate',
            DB::raw("CONCAT_WS(' ', people.paternal_last_name, people.maternal_last_name) as lastName"),
            DB::raw("CONCAT_WS(', ', locations.district, people.birth_location_detail) as birthLocation"),
        )->join('people', 'sacrament_roles.person_id', '=', 'people.id')
            ->leftJoin('locations', 'people.birth_location', '=', 'locations.id')
            ->leftJoin('countries', 'people.birth_country', '=', 'countries.id')
            ->whereIN('sacrament_roles.role', ['1', '2', '3'])
            ->where('sacrament_roles.sacrament_record_id', $id)
            ->orderBy('sacrament_roles.role')
            ->get();

        $fellowsArray = [];
        foreach ($fellows as $role) {
            $fellowsArray[$role->role] = [
                'name' => $role->name,
                'lastName' => $role->lastName,
                'birthLocation' => $role->birthLocation,
                'birthDate' =>  $role->birthDate ? Carbon::parse($role->birthDate)->locale('es')->isoFormat('D [de] MMMM, YYYY') : null,
            ];

            if (in_array($role->role, [1, 2, 3])) {
                $family = FamilyRelationship::where('person_id', $role->personId)
                    ->orderBy('relationship')
                    ->get();
                $familyArray = [];
                foreach ($family as $f) {

                    $relatedPerson = Person::select(
                        DB::raw("CONCAT_WS(' ', people.name, people.paternal_last_name, people.maternal_last_name) as name"),
                    )->where('id', $f->related_person_id)->first();

                    $familyArray[$f->relationship] = [
                        'name' => $relatedPerson->name,
                    ];
                }
                $fellowsArray[$role->role]['family'] = $familyArray;

                if ($data->sacramentType == '2' || $data->sacramentType == '4') {
                    $fellowsArray[$role->role]['baptism'] = SacramentRecord::select(
                        'sacraments.date as date',
                        'parishes.name as parish',
                    )
                        ->join('sacraments', 'sacrament_records.sacrament_id', '=', 'sacraments.id')
                        ->join('sacrament_roles', 'sacrament_records.id', '=', 'sacrament_roles.sacrament_record_id')
                        ->join('parishes', 'sacraments.parish_id', '=', 'parishes.id')
                        ->where('sacrament_roles.person_id', $role->personId)
                        ->where('sacraments.type', 1)
                        ->where('sacrament_roles.role', 1)
                        ->first();

                    if ($fellowsArray[$role->role]['baptism']) {
                        $fellowsArray[$role->role]['baptism']->date = Carbon::parse($fellowsArray[$role->role]['baptism']->date)->locale('es')->isoFormat('D [de] MMMM, YYYY');
                    }
                }
            }
        }

        $godparents = SacramentRole::select(
            DB::raw(" GROUP_CONCAT(DISTINCT IF(people.name IS NOT NULL, CONCAT_WS(' ', people.name, people.paternal_last_name, people.maternal_last_name), NULL) SEPARATOR ', ' ) AS 'godParents'"),
        )->join('people', 'sacrament_roles.person_id', '=', 'people.id')
            ->whereIN('sacrament_roles.role', ['4', '5'])
            ->where('sacrament_roles.sacrament_record_id', $id)
            ->groupBy('sacrament_roles.sacrament_record_id')
            ->first();

        $witnesses  = SacramentRole::select(
            DB::raw(" GROUP_CONCAT(DISTINCT IF(people.name IS NOT NULL, CONCAT_WS(' ', people.name, people.paternal_last_name, people.maternal_last_name), NULL) SEPARATOR ', ' ) AS 'godParents'"),
        )->join('people', 'sacrament_roles.person_id', '=', 'people.id')
            ->whereIN('sacrament_roles.role', ['6'])
            ->where('sacrament_roles.sacrament_record_id', $id)
            ->groupBy('sacrament_roles.sacrament_record_id')
            ->first();

        $data['fellows'] = $fellowsArray;
        $data['godparents'] = $godparents ? $godparents->godParents : "";
        $data['witness'] = $witnesses ? $witnesses->godParents : "";
        $data['sacramentDate'] = Carbon::parse($data->sacramentDate)->locale('es')->isoFormat('D [de] MMMM, YYYY');

        $data['printDate'] = [
            'dayName' => Carbon::now()->locale('es')->isoFormat('dddd'),
            'day' => Carbon::now()->locale('es')->isoFormat('D'),
            'month' => Carbon::now()->locale('es')->isoFormat('MMMM'),
            'year' => Carbon::now()->locale('es')->isoFormat('YYYY'),
        ];

        return $data;
    }

    public function printRecordWithData($id)
    {
        try {
            $user = Auth::user();

            DB::beginTransaction();

            $data = $this->printData($id);
            $data = $data->toArray();
            $sacrament = SacramentRecord::join('sacraments', 'sacrament_records.sacrament_id', '=', 'sacraments.id')
                ->where('sacrament_records.id', $data['id'])
                ->first();

            if ($data['observations'] != null || $data['observations'] != '') {
                SacramentRecord::where('id', $data['id'])->update(['observation' => $data['observations']]);
            }

            PrintingRecord::register($data, $user->id);

            if ($sacrament->type == 1) {
                $pdf = $this->generateCertificate($data, null, 'pdf.certificates.baptism')['pdf'];
            } else if ($sacrament->type == 2) {
                $pdf = $this->generateCertificate($data, null, 'pdf.certificates.confirmation')['pdf'];
            } else if ($sacrament->type == 4) {
                $pdf = $this->generateCertificate($data, null, 'pdf.certificates.marriage')['pdf'];
            }

            DB::commit();
            return response($pdf->output())
                ->header('Content-Type', 'application/pdf');
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error($e->getMessage(), 'Error al imprimir el registro');
        }
    }
}
