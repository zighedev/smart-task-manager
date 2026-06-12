<?php

use Illuminate\Support\Facades\Route;

Route::livewire('/', 'tasks-page')->middleware('auth');
Route::livewire('/dashboard', 'tasks-page')->middleware('auth');
Route::livewire('/tasks-panel', 'tasks-page')->middleware('auth');

Route::livewire('/login', 'auth.login')->name('login')->middleware('guest');
Route::livewire('/register', 'auth.register')->name('register')->middleware('guest');