<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

if (app()->environment('local')) {
    Route::get('/dev/admin-preview', function () {
        return view('admin.dashboard-placeholder');
    });
}
