<?php
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\RateLimiter;

new #[Layout('livewire.components.layouts.app')] #[Title('Панель управления — Smart Docs')]
class extends Component {
    use WithFileUploads;
    public $file;
    public array $documents = [];
    public $openedDocId = null;
    public string $successMessage = '';
    public bool $showSuccess = false;

    public function mount(): void { $this->loadDocuments(); }

    public function loadDocuments(): void {
        $oldDocs = $this->documents;
        
        $raw = DB::table('documents')->where('user_id', Auth::id())->orderBy('created_at', 'desc')->get();
        $this->documents = json_decode(json_encode($raw), true);

        // Умная проверка: если старый список существовал, ищем изменившиеся статусы
        if (!empty($oldDocs) && count($oldDocs) === count($this->documents)) {
            $updated = false;
            foreach ($this->documents as $index => $newDoc) {
                if (isset($oldDocs[$index]) && $oldDocs[$index]['status'] !== $newDoc['status']) {
                    $updated = true;
                    break;
                }
            }
            if ($updated) {
                $this->successMessage = 'Статус обновлен!';
                $this->showSuccess = true;
            } else {
                $this->successMessage = 'Статус уже актуален.';
                $this->showSuccess = true;
            }
        }
    }

    public function toggleDoc($id): void {
        $this->openedDocId = ($this->openedDocId == $id) ? null : $id;
        $this->showSuccess = false; // Скрываем плашку при просмотре деталей
    }

    public function deleteDocument($id): void {
        $doc = DB::table('documents')->where('id', $id)->where('user_id', Auth::id())->first();
        if ($doc) {
            Storage::delete($doc->file_path);
            DB::table('documents')->where('id', $id)->delete();
            if ($this->openedDocId == $id) { $this->openedDocId = null; }
            $this->loadDocuments();
            $this->successMessage = 'Документ успешно удален.';
            $this->showSuccess = true;
        }
    }

    public function uploadDocument(): void {
        $this->showSuccess = false;
        $limiterKey = 'upload-doc:' . Auth::id();
        if (RateLimiter::tooManyAttempts($limiterKey, 1)) {
            $seconds = RateLimiter::availableIn($limiterKey);
            $this->addError('file', "Подождите {$seconds} сек.");
            return;
        }
        $this->validate(['file' => 'required|file|max:10240|mimes:pdf,xlsx,docx,txt,csv']);
        RateLimiter::hit($limiterKey, 10);
        $originalName = $this->file->getClientOriginalName();
        $storedPath = $this->file->store('documents');

        $docId = DB::table('documents')->insertGetId([
            'user_id' => Auth::id(), 'title' => $originalName, 'file_path' => $storedPath,
            'status' => 'pending', 'created_at' => now(), 'updated_at' => now(),
        ]);
        \App\Jobs\ProcessDocument::dispatchSync($docId);
        $this->reset('file'); $this->loadDocuments();
        $this->successMessage = 'Документ успешно поставлен в очередь к ИИ!';
        $this->showSuccess = true;
    }
}; ?>
<div class="space-y-6">
    <!-- Адаптивный баннер -->
    <div class="bg-gradient-to-r from-indigo-600 to-blue-600 rounded-2xl p-6 text-white shadow-sm">
        <h1 class="text-xl md:text-2xl font-bold">Добро пожаловать в Smart Docs!</h1>
        <p class="text-indigo-100 text-sm mt-1">Корпоративный комбайн автоматического анализа документов.</p>
    </div>

    <!-- Умная динамическая система уведомлений (Статус обновлен / Статус актуален) -->
    @if ($showSuccess)
        <div class="p-4 bg-emerald-50 text-emerald-700 text-sm rounded-2xl font-semibold border border-emerald-100 shadow-sm animate-fade-in flex items-center space-x-2">
            <span>ℹ️</span>
            <span>{{ $successMessage }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- ЛЕВАЯ ЧАСТЬ: Загрузка документа с КРУПНЫМ названием файла -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 space-y-4 h-fit">
            <h3 class="text-lg font-bold text-gray-800">Загрузить документ</h3>
            <form wire:submit.prevent="uploadDocument" class="space-y-4">
                <div class="relative border-2 border-dashed border-gray-200 hover:border-indigo-500 rounded-xl p-6 text-center cursor-pointer bg-gray-50/50">
                    <input type="file" wire:model="file" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                    <span class="text-3xl block mb-2">@if($file) 📄 @else 📥 @endif</span>
                    <span class="text-sm font-medium text-indigo-600 block">@if($file) Файл готов к отправке @else Выберите файл @endif</span>
                    
                    <!-- КРУПНОЕ НАЗВАНИЕ ФАЙЛА (Заметное и читаемое) -->
                    <span class="mt-2 block text-base font-semibold text-indigo-700 break-all max-w-[260px] mx-auto px-2">
                        @if($file) {{ $file->getClientOriginalName() }} @else <span class="text-xs text-gray-400 font-normal">PDF, XLSX, DOCX, CSV, TXT (До 10МБ)</span> @endif
                    </span>
                </div>
                @error('file') <span class="text-red-500 text-xs mt-1 block font-medium">{{ $message }}</span> @enderror
                
                <div wire:loading wire:target="file" class="text-xs text-indigo-600 font-medium animate-pulse text-center w-full block">⏳ Загрузка на сервер...</div>
                
                <button type="submit" wire:loading.attr="disabled" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-3 px-4 rounded-xl text-sm transition active:scale-[0.98] disabled:bg-gray-300">Отправить на анализ ИИ</button>
            </form>
        </div>

        <!-- ПРАВАЯ ЧАСТЬ: Список последних документов -->
        <div class="lg:col-span-2 bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
                <h3 class="text-lg font-bold text-gray-800">Последние документы</h3>
                
                <button type="button" wire:click="loadDocuments" class="w-full sm:w-auto flex items-center justify-center space-x-2 text-sm font-semibold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 px-4 py-2.5 rounded-xl transition active:scale-[0.95]">
                    <span wire:loading.class="animate-spin" wire:target="loadDocuments" class="block">🔄</span>
                    <span>Обновить статус</span>
                </button>
            </div>
            
            @if(empty($documents))
                <div class="text-center py-12 text-gray-400 space-y-2">
                    <span class="text-4xl block">🔍</span>
                    <p class="text-sm">Вы еще не загрузили ни одного документа.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-gray-500">
                        <thead class="text-xs text-gray-400 uppercase bg-gray-50/70 rounded-lg">
                            <tr>
                                <th class="px-4 py-3">Название</th>
                                <th class="px-4 py-3">Статус</th>
                                <th class="px-4 py-3 text-right">Действия</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($documents as $doc)
                                @php $cleanStatus = trim($doc['status']); @endphp
                                <tr class="hover:bg-gray-50/70 transition cursor-pointer select-none">
                                    <td wire:click="toggleDoc({{ $doc['id'] }})" class="px-4 py-3 font-medium text-gray-800 max-w-[150px] sm:max-w-xs truncate flex items-center space-x-2">
                                        <svg class="w-4 h-4 text-gray-400 transform transition-transform duration-200 shrink-0 @if($openedDocId == $doc['id']) rotate-180 @endif" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                        <span class="truncate">{{ $doc['title'] }}</span>
                                    </td>
                                    
                                    <td wire:click="toggleDoc({{ $doc['id'] }})" class="px-4 py-3 text-xs">
                                        @if($cleanStatus == 'pending') <span class="px-2.5 py-1 bg-yellow-50 text-yellow-700 rounded-md font-medium animate-pulse">В очереди</span>
                                        @elseif($cleanStatus == 'processing') <span class="px-2.5 py-1 bg-blue-50 text-blue-700 rounded-md font-medium animate-pulse">Анализ ИИ...</span>
                                        @elseif($cleanStatus == 'completed') <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 rounded-md font-medium">Готово</span>
                                        @else <span class="px-2.5 py-1 bg-red-50 text-red-700 rounded-md font-medium">Ошибка</span> @endif
                                    </td>
                                    
                                    <td class="px-4 py-3 text-right">
                                        <button type="button" wire:click.stop="deleteDocument({{ $doc['id'] }})" class="text-red-500 hover:text-red-700 bg-red-50 hover:bg-red-100 p-2 rounded-lg transition duration-150 active:scale-[0.9]">
                                            🗑<span class="hidden sm:inline ml-1 text-xs font-medium">Удалить</span>
                                        </button>
                                    </td>
                                </tr>
                                
                                @if($openedDocId == $doc['id'] && $cleanStatus == 'completed')
                                    <tr class="bg-gray-50/40">
                                        <td colspan="3" class="px-6 py-4 border-t border-b border-gray-100/50">
                                            <div class="space-y-3">
                                                <div class="flex items-center space-x-2 text-xs font-bold text-indigo-600 uppercase tracking-wider">🤖 <span>Аналитический отчет локального ИИ (Qwen 2.5)</span></div>
                                                <p class="text-sm text-gray-700 leading-relaxed bg-white p-4 rounded-xl border border-gray-100 shadow-sm whitespace-pre-line">{{ $doc['ai_summary'] ?? 'Отчет пуст.' }}</p>
                                                @if($doc['ai_extracted_data'])
                                                    @php $meta = json_decode($doc['ai_extracted_data'], true); @endphp
                                                    <div class="flex flex-wrap gap-2 pt-1">
                                                        <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded-md font-medium">Движок: {{ $meta['ai_engine'] ?? 'Неизвестен' }}</span>
                                                        <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded-md font-medium">Тип данных: {{ $meta['file_type'] ?? 'Документ' }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
