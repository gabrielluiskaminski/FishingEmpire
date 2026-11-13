<?php

use Illuminate\Support\Facades\Route;

// O frontend da loja vive exclusivamente em /front (Vite + Bootstrap + Axios).
// Nenhuma view Blade é usada para renderizar a aplicação.
Route::get('/', fn () => response()->json([
    'application' => 'Fishing Empire API',
    'status' => 'online',
    'frontend' => 'http://localhost:5500',
    'catalog' => '/api/products',
]));
