<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use App\Http\Controllers\ProjectController;
use App\Http\Controllers\CatalogsController;
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

Route::get('/manager/{id}', function ($id) {
    return Inertia::render('Manager', [
        'projectId' => $id
    ]);
})->name('manager.show');

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
