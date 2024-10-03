<?php

use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\VoucherController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('user-voucher', [VoucherController::class, 'codeVoucher'])->name('codeVoucher');
    Route::post('voucher-schedule', [VoucherController::class, 'runVoucherSchedule'])->name('runVoucherSchedule');

    Route::get('product', [ProductController::class, 'index'])->name('product.index');
    Route::post('product', [ProductController::class, 'store'])->name('product.store');
    Route::put('product/{id}', [ProductController::class, 'update'])->name('product.update');
    Route::delete('product/{id}', [ProductController::class, 'destroy'])->name('product.destroy');

    Route::get('voucher', [VoucherController::class, 'index'])->name('voucher.index');
    Route::post('voucher', [VoucherController::class, 'store'])->name('voucher.store');
    Route::put('voucher/{id}', [VoucherController::class, 'update'])->name('voucher.update');
    Route::delete('voucher/{id}', [VoucherController::class, 'destroy'])->name('voucher.destroy');

});
Route::get('logout', [AuthenticationController::class, 'logout'])->name('logout');

Route::post('register', [AuthenticationController::class, 'register'])->name('register');
Route::post('login', [AuthenticationController::class, 'login'])->name('login');
