<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

new #[Layout('livewire.components.layouts.app')] #[Title('Вход в систему — Smart Docs')]
class extends Component {
    public string $email = '';
    public string $password = '';

    // Метод авторизации
    public function login()
    {
        // Базовая валидация полей
        $this->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Пытаемся авторизовать пользователя через стандартный механизм Laravel
        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password])) {
            throw ValidationException::withMessages([
                'email' => __('Неверный email или пароль.'),
            ]);
        }

        // Запрашиваем обновление сессии для безопасности
        session()->regenerate();

        // Перенаправляем сотрудника в личный кабинет!
        return redirect()->intended('/dashboard');
    }
}; ?>

<div class="max-w-md mx-auto bg-white p-6 md:p-8 rounded-2xl shadow-sm border border-gray-100 mt-4 md:mt-12">
    <h2 class="text-2xl font-bold text-gray-800 mb-2 text-center">Вход в систему</h2>
    <p class="text-sm text-gray-400 text-center mb-6">Smart Docs — корпоративный документооборот</p>

    <form wire:submit.prevent="login" class="space-y-4">
        <!-- Поле Email -->
        <div>
            <label class="block text-sm font-medium text-gray-700">Email адрес</label>
            <input type="email" wire:model="email" class="mt-1 block w-full rounded-xl border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-gray-50 p-3 border text-sm">
            @error('email') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
        </div>

        <!-- Поле Пароля -->
        <div>
            <label class="block text-sm font-medium text-gray-700">Пароль</label>
            <input type="password" wire:model="password" class="mt-1 block w-full rounded-xl border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-gray-50 p-3 border text-sm">
            @error('password') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
        </div>

        <!-- Кнопка Входа (Крупная, удобная для нажатия пальцем на телефоне) -->
        <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-3 px-4 rounded-xl text-sm transition duration-150 active:scale-[0.99]">
            Войти в кабинет
        </button>
    </form>

    <p class="mt-6 text-sm text-gray-500 text-center">
        Еще нет аккаунта? <a href="/register" class="text-indigo-600 font-medium hover:underline">Регистрация</a>
    </p>
</div>
