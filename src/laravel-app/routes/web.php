<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

// Route::get('/', function () {
//     return view('welcome');
// });



// Направляем корень сайта напрямую на Volt компонент без контроллеров
Volt::route('/', 'counter');

// Защищенные корпоративные роуты
Route::middleware(['auth'])->group(function () {
    Volt::route('/dashboard', 'dashboard')->name('dashboard');
    Volt::route('/profile', 'profile')->name('profile'); // Наш новый роут профиля
});
