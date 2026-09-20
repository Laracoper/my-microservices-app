<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

new #[Layout('livewire.components.layouts.app')] #[Title('Smart Docs — Корпоративный документооборот')]
class extends Component {
    // Логика счетчика удалена, здесь чистая презентационная страница
}; ?>

<div class="space-y-12 py-4 md:py-12">
    <!-- Главный баннер (Hero Section) -->
    <div class="flex flex-col lg:flex-row items-center justify-between gap-8">
        <div class="space-y-6 max-w-xl text-center lg:text-left">
            <h1 class="text-3xl md:text-5xl font-extrabold text-gray-900 leading-tight">
                Автоматизируйте работу с документами с помощью <span class="text-indigo-600">локального ИИ</span>
            </h1>
            <p class="text-base md:text-lg text-gray-600">
                Загружайте договора, акты и отчеты. Наша изолированная нейросеть мгновенно извлечет ключевые данные, проверит риски и структурирует информацию без отправки данных в интернет.
            </p>
            <div class="flex flex-col sm:flex-row justify-center lg:justify-start gap-4 pt-2">
                @auth
                    <a href="/dashboard" class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-3 px-6 rounded-xl text-center transition duration-150 active:scale-[0.98]">
                        Перейти в Личный кабинет
                    </a>
                @else
                    <a href="/register" class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-3 px-6 rounded-xl text-center transition duration-150 active:scale-[0.98]">
                        Начать использование
                    </a>
                    <a href="/login" class="bg-white hover:bg-gray-50 text-gray-700 font-medium py-3 px-6 rounded-xl text-center border border-gray-200 transition duration-150">
                        Войти для сотрудников
                    </a>
                @endauth
            </div>
        </div>
        
        <!-- Большая интерактивная иконка вместо тяжелой картинки -->
        <div class="text-9xl md:text-[12rem] animate-pulse select-none p-4 bg-white rounded-3xl shadow-sm border border-gray-100">
            🤖
        </div>
    </div>

    <!-- Блок преимуществ (Features Section) -->
    <div class="space-y-6 pt-6">
        <h2 class="text-2xl font-bold text-gray-800 text-center">Почему компании выбирают Smart Docs?</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Преимущество 1 -->
            <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm space-y-3">
                <div class="text-3xl p-3 bg-red-50 text-red-600 rounded-xl w-fit">🔒</div>
                <h3 class="font-bold text-gray-800 text-lg">100% Конфиденциально</h3>
                <p class="text-sm text-gray-500 leading-relaxed">
                    ИИ-модель развернута внутри закрытого Docker-контейнера в офисе компании. Никакие коммерческие данные не утекают в сеть.
                </p>
            </div>

            <!-- Преимущество 2 -->
            <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm space-y-3">
                <div class="text-3xl p-3 bg-indigo-50 text-indigo-600 rounded-xl w-fit">⚡</div>
                <h3 class="font-bold text-gray-800 text-lg">Асинхронная обработка</h3>
                <p class="text-sm text-gray-500 leading-relaxed">
                    Благодаря связке Laravel + Redis + Python тяжелые файлы анализируются в фоновом режиме, не замедляя работу интерфейса.
                </p>
            </div>

            <!-- Преимущество 3 -->
            <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm space-y-3">
                <div class="text-3xl p-3 bg-emerald-50 text-emerald-600 rounded-xl w-fit">📊</div>
                <h3 class="font-bold text-gray-800 text-lg">Удобный дашборд</h3>
                <p class="text-sm text-gray-500 leading-relaxed">
                    Реактивный Mobile-First интерфейс на Livewire 4 позволяет руководителям изучать аналитику по договорам прямо со смартфонов.
                </p>
            </div>
        </div>
    </div>
</div>
