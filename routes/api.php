<?php

use App\Http\Controllers\Core\AuthController;
use App\Http\Controllers\Core\PermissionController;
use App\Http\Controllers\Core\RoleController;
use App\Http\Controllers\Core\UserController;
use App\Http\Controllers\MinisterController;
use App\Http\Controllers\ParishController;
use App\Http\Controllers\PersonController;
use App\Http\Controllers\SacramentBookController;
use App\Http\Controllers\SacramentController;
use App\Http\Controllers\SacramentRecordController;
use App\Http\Controllers\SelectItemsController;
use Barryvdh\DomPDF\PDF;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'auth'], function () {
    Route::post('/sign-in', [AuthController::class, 'signIn']);
    Route::post('/sign-out', [AuthController::class, 'signOut'])->middleware('auth:sanctum');
    Route::get('/user', [AuthController::class, 'user'])->middleware('auth:sanctum');
});


//users
Route::group(['prefix' => 'users'], function () {
    Route::post('/load-data-table', [UserController::class, 'loadDataTable'])->middleware('auth:sanctum');
    Route::post('/save', [UserController::class, 'save'])->middleware('auth:sanctum');
    Route::delete('/delete/{id}', [UserController::class, 'delete'])->middleware('auth:sanctum');
});

//roles
Route::group(['prefix' => 'roles'], function () {
    Route::post('/load-data-table', [RoleController::class, 'loadDataTable']);
    Route::post('/save', [RoleController::class, 'save']);
    Route::delete('/delete/{id}', [RoleController::class, 'delete']);
    Route::get('/all-permissions', [PermissionController::class, 'allPermissions']);
    Route::post('/assign-permissions', [RoleController::class, 'assignPermissions']);
})->middleware('auth:sanctum');

//permissions
Route::group(['prefix' => 'permissions'], function () {
    Route::post('/load-data-table', [PermissionController::class, 'loadDataTable'])->middleware('auth:sanctum');
    Route::post('/save', [PermissionController::class, 'save'])->middleware('auth:sanctum');
});


//sacramental books
Route::group(['prefix' => 'sacramental-books'], function () {
    Route::post('/load-data-table', [SacramentBookController::class, 'loadDataTable'])->middleware('auth:sanctum');
    Route::post('/save', [SacramentBookController::class, 'save'])->middleware('auth:sanctum');
    Route::delete('/delete/{id}', [SacramentBookController::class, 'delete'])->middleware('auth:sanctum');
    //getBookNumbering
    Route::get('/get-book-numbering/{id}', [SacramentBookController::class, 'getBookNumbering'])->middleware('auth:sanctum');
});

//ministers
Route::group(['prefix' => 'ministers'], function () {
    Route::post('/load-data-table', [MinisterController::class, 'loadDataTable'])->middleware('auth:sanctum');
    Route::post('/save', [MinisterController::class, 'save'])->middleware('auth:sanctum');
    Route::delete('/delete/{id}', [MinisterController::class, 'delete'])->middleware('auth:sanctum');
});

//parishes
Route::group(['prefix' => 'parishes'], function () {
    Route::post('/load-data-table', [ParishController::class, 'loadDataTable'])->middleware('auth:sanctum');
    Route::post('/save', [ParishController::class, 'save'])->middleware('auth:sanctum');
    Route::delete('/delete/{id}', [ParishController::class, 'delete'])->middleware('auth:sanctum');
});


//sacraments
Route::group(['prefix' => 'sacraments'], function () {
    Route::post('/load-data-table/{type}', [SacramentController::class, 'loadDataTable'])->middleware('auth:sanctum');
    Route::post('/save', [SacramentController::class, 'save'])->middleware('auth:sanctum');
    Route::delete('/delete/{id}', [SacramentController::class, 'delete'])->middleware('auth:sanctum');
    Route::get('/get-sacrament-by-id/{id}', [SacramentController::class, 'getSacramentById'])->middleware('auth:sanctum');
});

Route::group(['prefix' => 'sacrament-records'], function () {
    Route::post('/load-data-table/{sacramentId}', [SacramentRecordController::class, 'loadDataTable'])->middleware('auth:sanctum');
    Route::post('/save', [SacramentRecordController::class, 'save'])->middleware('auth:sanctum');
    Route::get('/get-record-by-id/{id}', [SacramentRecordController::class, 'getRecordById'])->middleware('auth:sanctum');
    Route::post('/invalidate', [SacramentRecordController::class, 'invalidate'])->middleware('auth:sanctum');
    Route::delete('/delete/{id}', [SacramentRecordController::class, 'delete'])->middleware('auth:sanctum');

    Route::post('/get-print-data', [SacramentRecordController::class, 'getPrintData'])->middleware('auth:sanctum');
    Route::post('/print-record', [SacramentRecordController::class, 'printRecord'])->middleware('auth:sanctum');
    Route::get('/get-baptism-person/{personId}', [SacramentRecordController::class, 'getBaptismPerson'])->middleware('auth:sanctum');

    //saveExternalBaptism
    Route::post('/save-external-baptism', [SacramentRecordController::class, 'saveExternalBaptism'])->middleware('auth:sanctum');
    //searchActs
    Route::get('/search-acts/{search}', [SacramentRecordController::class, 'searchActs'])->middleware('auth:sanctum');
    //reportCountRecordByType
    Route::get('/report-count-record-by-type', [SacramentRecordController::class, 'reportCountRecordByType'])->middleware('auth:sanctum');
});

//people
Route::group(['prefix' => 'people'], function () {
    Route::post('/load-data-table', [PersonController::class, 'loadDataTable'])->middleware('auth:sanctum');
    Route::post('/save', [PersonController::class, 'save'])->middleware('auth:sanctum');
    Route::delete('/delete/{id}', [PersonController::class, 'delete'])->middleware('auth:sanctum');
    Route::get('/search-select/{search}', [PersonController::class, 'searchSelect'])->middleware('auth:sanctum');
    Route::get('/get-family-relationships/{id}', [PersonController::class, 'getFamilyRelationships'])->middleware('auth:sanctum');
    Route::get('/get-person-by-id/{id}', [PersonController::class, 'getPersonById'])->middleware('auth:sanctum');
});


//select items
Route::group(['prefix' => 'select-items'], function () {
    Route::get('/roles', [SelectItemsController::class, 'rolesItems']);
    Route::get('/books/{type}/{search}', [SelectItemsController::class, 'booksItems']);
    Route::get('/ministers', [SelectItemsController::class, 'ministersItems']);
    Route::get('/parishes/{search}', [SelectItemsController::class, 'parishesItems']);
});


//dompdf test
Route::get('dompdf-test', function () {
    $pdf = app(PDF::class);

    $background = public_path('templates/confirmation.jpeg');
    //margin
    $pdf->setOptions([
        'margin-top' => 0,
        'margin-right' => 0,
        'margin-bottom' => 0,
        'margin-left' => 0,
        'isRemoteEnabled' => true
    ]);
    //add style
    $pdf->loadView('pdf.certificates.confirmation', [
        'name' => 'John Doe',
        'date' => '2021-01-01',
        'minister' => 'Rev. Fr. John Doe',
        'book' => 'Book 1',
        'page' => 'Page 1',
        'entry' => 'Entry 1',
        'background' => 'http://lnx.simple.auth.test/templates/confirmation.jpeg'
    ]);
    return $pdf->stream();
});
