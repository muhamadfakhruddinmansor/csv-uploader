<?php

use App\Http\Controllers\UploadController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;

Route::get('/', [UploadController::class, 'index'])->name('uploads.index');
Route::post('/upload', [UploadController::class, 'store'])->name('uploads.store');
Route::get('/api/uploads', [UploadController::class, 'list']);
Route::get('/products', [ProductController::class, 'index'])->name('products.index');

