<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\chatController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;



Route::controller(AuthController::class)->group(function () {
    Route::get('/', 'home')->name('home');
    Route::post('login', 'login')->name('login');
    Route::get('logout', 'logout')->name('logout');
});

Route::get('dashboard',[DashboardController::class,'dashboard'])->name('dashboard');
Route::get('chat',[chatController::class,'chat'])->name('chat');
Route::get('messages/{id}',[chatController::class,'message'])->name('message');
Route::post('send-mesage',[chatController::class,'sendMessage'])->name('send.message');
