<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Redis;

class ProcessDocument implements ShouldQueue
{
    use Queueable;

    // В конструктор мы передадим ID документа, который только что создался в MySQL
    public function __construct(
        public int $documentId
    ) {}

    // Метод handle() выполняется, когда Laravel запускает эту задачу
    public function handle(): void
    {
        // 1. Находим документ в базе данных, чтобы узнать путь к файлу
        $document = \Illuminate\Support\Facades\DB::table('documents')
            ->where('id', $this->documentId)
            ->first();

        if (!$document) {
            return;
        }

        // 2. Меняем статус документа на "processing" (Анализ ИИ...)
        \Illuminate\Support\Facades\DB::table('documents')
            ->where('id', $this->documentId)
            ->update([
                'status' => 'processing',
                'updated_at' => now()
            ]);

        // 3. Формируем чистый JSON-пакет для Python-микросервиса
        $taskData = json_encode([
            'event' => 'document_uploaded',
            'document_id' => $document->id,
            'file_path' => $document->file_path, // Например: documents/abcde123.csv
            'title' => $document->title,
        ]);

        // 4. Публикуем задачу напрямую в Redis в список 'python_queue' без префиксов
        Redis::rpush('python_queue', $taskData);
    }
}
