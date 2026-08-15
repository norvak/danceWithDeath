<?php

use Illuminate\Support\Facades\Route;

Route::get('/', fn () => response()->json([
    'name' => 'Dance with Death API',
    'status' => 'ok',
]));
