<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PetController;

Route::get('/', function () {
    return view('pets');
});

Route::get('/api/pet', [PetController::class, 'getPet']);
