<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReservaController;
use App\Http\Controllers\HuespedController;
use App\Http\Controllers\EstadiaController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/reservas', [ReservaController::class, 'index'])
    ->name('reservas.index');

Route::get('/reservas/crear', [ReservaController::class, 'create'])
    ->name('reservas.create');

Route::post('/reservas', [ReservaController::class, 'store'])
    ->name('reservas.store');

Route::get('/huespedes', [HuespedController::class, 'index'])
    ->name('huespedes.index');

Route::get('/huespedes/crear', [HuespedController::class, 'create'])
    ->name('huespedes.create');

Route::post('/huespedes', [HuespedController::class, 'store'])
    ->name('huespedes.store');

Route::get('/estadias', [EstadiaController::class, 'index'])
    ->name('estadias.index');

Route::post('/estadias/{id}/checkin', [EstadiaController::class, 'checkin'])
    ->name('estadias.checkin');

Route::post('/estadias/{id}/checkout', [EstadiaController::class, 'checkout'])
    ->name('estadias.checkout');