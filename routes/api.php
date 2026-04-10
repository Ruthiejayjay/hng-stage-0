<?php

use App\Http\Controllers\ClassifyController;
use Illuminate\Support\Facades\Route;

Route::get('/classify', [ClassifyController::class, 'classify']);