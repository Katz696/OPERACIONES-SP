<?php

use App\Http\Controllers\AgreementController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use App\Http\Controllers\ProjectController;
use App\Http\Controllers\CatalogsController;
use App\Http\Controllers\SnapProjectController;
use Illuminate\Routing\Route as RoutingRoute;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');


Route::prefix('catalogs')->name('catalogs.')->group(function () {
    Route::get('/projects', [CatalogsController::class, 'getProjects'])->name('projects');
    Route::get('/users', [CatalogsController::class, 'getUsers'])->name('users');
    Route::get('/customers', [CatalogsController::class, 'getCustomers'])->name('customers');
    Route::put('/projects/sync', [CatalogsController::class, 'updateProjects'])
        ->name('projects.sync');
    Route::put('/users/sync', [CatalogsController::class, 'updateUsers'])
        ->name('users.sync');
        Route::put('/customer/sync', [CatalogsController::class, 'updateCustomers'])
        ->name('customers.sync');
});

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('catalogs', function () {
    return Inertia::render('Catalogs');
})->middleware(['auth', 'verified'])->name('catalogs');

Route::get('/project', [ProjectController::class, 'showTree'])->name('projects.show');
Route::post('/project/update', [ProjectController::class, 'updateTree'])->name('projects.update');
Route::get('/project/history', [ProjectController::class, 'history'])->name('projects.history');
Route::get('/project/{id}/snaps', [SnapProjectController::class, 'index'])->name('project.snaps');
Route::post('/project/snaps', [SnapProjectController::class, 'store'])->name('project.snaps.store');
Route::delete('/project/snaps/{id}', [SnapProjectController::class, 'destroy'])->name('snaps.destroy');

Route::get('/project/{id}/agreements',[AgreementController::class,'index'])->name('project.agreements');
Route::post('/project/agreements/store', [AgreementController::class,'store'])->name('project.agreements.store');
Route::delete('/project/agreement/{id}', [AgreementController::class, 'destroy'])->name('project.agreements.destroy');


Route::get('/manager/{id}', function ($id) {
    return Inertia::render('Manager', [
        'projectId' => $id
    ]);
})->name('manager.show');

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
