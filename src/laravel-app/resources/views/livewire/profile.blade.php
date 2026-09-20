<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;
use Laravel\Fortify\Contracts\UpdatesUserPasswords;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

new #[Layout('livewire.components.layouts.app')] #[Title('Настройки профиля — Smart Docs')]
class extends Component {
    // Данные для обновления профиля
    public string $name = '';
    public string $email = '';

    // Данные для смены пароля
    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    // Инициализация данных при загрузке страницы
    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
    }

    // 1. Метод обновления Имени и Email
    public function updateProfile(UpdatesUserProfileInformation $updater): void
    {
        $updater->update(Auth::user(), [
            'name' => $this->name,
            'email' => $this->email,
        ]);

        session()->flash('profile-status', 'Данные профиля успешно обновлены.');
    }

    // 2. Метод смены пароля
    public function updatePassword(UpdatesUserPasswords $updater): void
    {
        $updater->update(Auth::user(), [
            'current_password' => $this->current_password,
            'password' => $this->password,
            'password_confirmation' => $this->password_confirmation,
        ]);

        // Очищаем поля формы после успешной смены
        $this->reset(['current_password', 'password', 'password_confirmation']);

        session()->flash('password-status', 'Пароль успешно изменен.');
    }
}; ?>

<div class="max-w-3xl mx-auto space-y-6 pb-12">
    
    <!-- Заголовок страницы -->
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Настройки профиля</h1>
        <p class="text-sm text-gray-500">Управление личной информацией и безопасностью вашей учетной записи.</p>
    </div>

    <!-- БЛОК 1: Основная информация (Имя, Email) -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 space-y-4">
        <h3 class="text-lg font-bold text-gray-800">Информация профиля</h3>
        <p class="text-xs text-gray-400">Обновите имя вашей учетной записи и адрес электронной почты.</p>

        @if (session()->has('profile-status'))
            <div class="p-3 bg-emerald-50 text-emerald-700 text-sm rounded-xl font-medium">{{ session('profile-status') }}</div>
        @endif

        <form wire:submit.prevent="updateProfile" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Ваше имя</label>
                    <input type="text" wire:model="name" class="mt-1 block w-full rounded-xl border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-gray-50 p-3 border text-sm">
                    @error('name') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Email адрес</label>
                    <input type="email" wire:model="email" class="mt-1 block w-full rounded-xl border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-gray-50 p-3 border text-sm">
                    @error('email') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2.5 px-6 rounded-xl text-sm transition duration-150 active:scale-[0.98]">
                Сохранить изменения
            </button>
        </form>
    </div>

    <!-- БЛОК 2: Смена пароля -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 space-y-4">
        <h3 class="text-lg font-bold text-gray-800">Обновление пароля</h3>
        <p class="text-xs text-gray-400">Убедитесь, что ваша учетная запись использует длинный, случайный пароль для безопасности.</p>

        @if (session()->has('password-status'))
            <div class="p-3 bg-emerald-50 text-emerald-700 text-sm rounded-xl font-medium">{{ session('password-status') }}</div>
        @endif

        <form wire:submit.prevent="updatePassword" class="space-y-4 max-w-md">
            <div>
                <label class="block text-sm font-medium text-gray-700">Текущий пароль</label>
                <input type="password" wire:model="current_password" class="mt-1 block w-full rounded-xl border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-gray-50 p-3 border text-sm">
                @error('current_password') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Новый пароль</label>
                <input type="password" wire:model="password" class="mt-1 block w-full rounded-xl border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-gray-50 p-3 border text-sm">
                @error('password') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Подтвердите новый пароль</label>
                <input type="password" wire:model="password_confirmation" class="mt-1 block w-full rounded-xl border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-gray-50 p-3 border text-sm">
            </div>
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2.5 px-6 rounded-xl text-sm transition duration-150 active:scale-[0.98]">
                Изменить пароль
            </button>
        </form>
    </div>
</div>
