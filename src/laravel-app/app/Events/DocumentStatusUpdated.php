<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DocumentStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    // В конструктор передаем ID пользователя, чтобы сигнал долетел именно на его смартфон/ПК
    public function __construct(
        public int $userId
    ) {}

    // Указываем публичный канал вещания для конкретного сотрудника
        public function broadcastOn(): array
    {
        // Делаем общий публичный канал для мгновенных уведомлений
        return [
            new Channel('public-docs'),
        ];
    }


    // Задаем точное имя события, которое будет слушать фронтенд Livewire 4
    public function broadcastAs(): string
    {
        return 'status.updated';
    }
}
