<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            // Привязываем документ к конкретному сотруднику
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('title');             // Оригинальное имя файла (например, Договор_№5.pdf)
            $table->string('file_path');         // Путь к файлу в хранилище внутри Docker
            $table->string('status')->default('pending'); // Статус: pending, processing, completed, failed

            // Поля, которые заполнит наш локальный ИИ на Python:
            $table->text('ai_summary')->nullable();       // Краткое описание договора
            $table->json('ai_extracted_data')->nullable(); // Извлеченные ИНН, суммы, даты в JSON

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
