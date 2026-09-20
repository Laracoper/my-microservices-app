<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Illuminate\Support\Facades\Auth;

new #[Layout('livewire.components.layouts.app')] #[Title('Регистрация в системе')] 
class extends Component {
    // В этом массиве будут храниться данные формы (двустороннее связывание wire:model)
    public array $state = [
        'name' => '',
        'email' => '',
        'password' => '',
        'password_confirmation' => '',
    ];

    // Метод регистрации
    public function register(CreatesNewUsers$creator)
    {
        // Вызываем логику создания пользователя Fortify
        // Она сама внутри себя проверит валидацию (имя, уникальный email, совпадение паролей)
        $user = $creator->create($this->state);

        // Авторизуем созданного пользователя в сессии
        Auth::login($user);

        // Перенаправляем на главную страницу (к нашему счетчику)
        return redirect()->to('/dashboard');
    }
}; ?>

<div class="max-w-md mx-auto bg-white p-8 rounded-xl shadow-md">
    <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Создать аккаунт</h2>

    <form wire:submit.prevent="register" class="space-y-4">
        <!-- Поле Имени -->
        <div>
            <label class="block text-sm font-medium text-gray-700">Ваше имя</label>
            <input type="text" wire:model="state.name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-gray-50 p-2.5 border">
            @error('name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
        </div>

        <!-- Поле Email -->
        <div>
            <label class="block text-sm font-medium text-gray-700">Email адрес</label>
            <input type="email" wire:model="state.email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-gray-50 p-2.5 border">
            @error('email') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
        </div>

        <!-- Поле Пароля -->
        <div>
            <label class="block text-sm font-medium text-gray-700">Пароль</label>
            <input type="password" wire:model="state.password" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-gray-50 p-2.5 border">
            @error('password') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
        </div>

        <!-- Повторение Пароля -->
        <div>
            <label class="block text-sm font-medium text-gray-700">Подтвердите пароль</label>
            <input type="password" wire:model="state.password_confirmation" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-gray-50 p-2.5 border">
        </div>

        <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2.5 px-4 rounded-md transition duration-200">
            Зарегистрироваться
        </button>
    </form>

    <p class="mt-4 text-sm text-gray-600 text-center">
        Уже есть аккаунт? <a href="/login" class="text-indigo-600 hover:underline">Войти</a>
    </p>
</div>
