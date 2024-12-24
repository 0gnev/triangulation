<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TriangulationController;

Route::post('/triangulate', [TriangulationController::class, 'triangulate']);
