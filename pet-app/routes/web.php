<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PetController;

// Home → redirect to the pets listing page
Route::get('/', [PetController::class, 'index'])->name('home');

// Pets CRUD resource routes
Route::get('/pets', [PetController::class, 'index'])->name('pets.index');
Route::post('/pets', [PetController::class, 'store'])->name('pets.store');
Route::get('/pets/{pet}', [PetController::class, 'show'])->name('pets.show');
Route::put('/pets/{pet}', [PetController::class, 'update'])->name('pets.update');
Route::delete('/pets/{pet}', [PetController::class, 'destroy'])->name('pets.destroy');

// API route for pet facts (used by front-end JS via fetch)
Route::get('/api/pet', [PetController::class, 'getPet'])->name('api.pet');