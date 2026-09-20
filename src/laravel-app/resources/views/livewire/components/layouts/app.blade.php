<?php
// Каноничный способ заставить Vite генерировать абсолютные пути от корня нашего Nginx прокси
\Illuminate\Support\Facades\Vite::useHotFile(public_path('hot'));
?>
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Мой проект' }}</title>

    <!-- Включаем ваш родной Tailwind v4 через сборщик Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body class="bg-gray-100 font-sans">

    <nav class="bg-white shadow mb-6">
        <!-- px-4 на мобилках, max-w-6xl mx-auto для больших мониторов -->
        <div class="max-w-6xl mx-auto px-4 py-4 flex flex-row justify-between items-center">
            <a href="{{ auth()->check() ? '/dashboard' : '/' }}"
                class="font-bold text-xl text-indigo-600 tracking-tight">Smart Docs</a>

            <div class="flex items-center space-x-3 md:space-x-4">
                @auth
                    <!-- Ссылка на Дашборд -->
                    <a href="/dashboard" class="text-sm font-medium text-gray-600 hover:text-indigo-600">Панель</a>

                    <!-- Ссылка на Настройки Профиля -->
                    <a href="/profile" class="text-sm font-medium text-gray-600 hover:text-indigo-600">Профиль</a>

                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit"
                            class="text-sm font-medium text-red-500 bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-lg transition duration-150">Выйти</button>
                    </form>
                @else
                    <a href="/login" class="text-sm font-medium text-gray-600 hover:text-indigo-600 px-2 py-1">Вход</a>
                    <a href="/register"
                        class="bg-indigo-600 text-white px-3 py-2 rounded-xl text-sm font-medium hover:bg-indigo-700 transition duration-150">Регистрация</a>
                @endauth
            </div>

        </div>
    </nav>



    <main class="max-w-6xl mx-auto px-4">
        {{ $slot }}
    </main>

    @livewireScripts
</body>

</html>
