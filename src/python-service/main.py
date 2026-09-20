import os
import time
import json
import redis
import requests
import mysql.connector
import pandas as pd

# 1. Подключаемся к Redis (используем имя сервиса из docker-compose)
r = redis.Redis(host='redis', port=6379, db=0, decode_responses=True)

# 2. Функция для безопасного подключения к MySQL
def get_db_connection():
    return mysql.connector.connect(
        host="mysql",
        user=os.environ.get("DB_USERNAME", "laravel"),
        password=os.environ.get("DB_PASSWORD", "password"),
        database=os.environ.get("DB_DATABASE", "micro_laravel")
    )

print("🚀 Python-микросервис Smart Docs успешно запущен и слушает Redis...", flush=True)

while True:
    try:
        # Безопасное блокирующее чтение очереди Redis (0% нагрузки на CPU в ожидании)
        task = r.blpop('python_queue', timeout=0)
        if not task:
            continue
            
        data = json.loads(task[1]) # Извлекаем тело сообщения из кортежа blpop
        doc_id = data['document_id']
        file_path = data['file_path']
        title = data['title']
        
        print(f"📥 Поступил документ на анализ ID {doc_id}: {title}", flush=True)
        
        # Вычисляем точный физический путь к файлу внутри контейнера Python
        actual_filename = os.path.basename(file_path)
        full_physical_path = f"/app/storage/documents/{actual_filename}"

        # Инициализируем переменные для отчета
        ai_summary = ""
        extracted_data = {}
        
        # Проверяем, существует ли файл физически на общем диске
        if os.path.exists(full_physical_path):
            print(f"🔎 Файл найден. Подготавливаем контекст для Qwen 2.5...", flush=True)
            
            # 1. Читаем содержимое файла (берём первые 3000 символов, чтобы не перегружать RAM процессора)
            if title.lower().endswith('.csv'):
                try:
                    df = pd.read_csv(full_physical_path)
                    text_to_analyze = df.head(10).to_string() # Передаем ИИ первые 10 строк таблицы
                except Exception as e:
                    text_to_analyze = f"Не удалось прочитать CSV через Pandas: {str(e)}"
            else:
                try:
                    with open(full_physical_path, 'r', encoding='utf-8', errors='ignore') as f:
                        text_to_analyze = f.read(1000)
                except Exception as e:
                    text_to_analyze = f"Не удалось прочитать текстовый файл: {str(e)}"

            # 2. Формируем строгий системный промпт для офисного ИИ-аналитика
            system_prompt = (
                "Ты профессиональный корпоративный юрист и аналитик данных компании. "
                "Проанализируй предоставленный текст документа и сделай краткую, емкую сводку на РУССКОМ языке. "
                "Обязательно выдели: 1. Тип документа, 2. Ключевые участники или контрагенты, "
                "3. Главную суть, предмет сделки или важные риски. Будь лаконичен, пиши строго по делу без лишней воды."
            )
            
            print("🧠 Отправляем запрос в контейнер Ollama...", flush=True)
            
            # 3. Делаем HTTP-запрос к нашему изолированному микросервису Ollama по внутренней сети Docker
            try:
                response = requests.post(
                    "http://ollama:11434/api/generate",
                    json={
                        "model": "qwen2.5:1.5b",
                        "prompt": f"{system_prompt}\n\nТекст документа для анализа:\n{text_to_analyze}",
                        "stream": False # Ждем полный ответ одной строкой, отключая потоковое вещание
                    },
                    timeout=None # Даем ИИ запас времени на генерацию на обычном CPU
                )
                
                if response.status_code == 200:
                    ai_result = response.json().get('response', '')
                    ai_summary = ai_result
                    extracted_data = {
                        "file_type": "CSV Table" if title.lower().endswith('.csv') else "Document",
                        "ai_engine": "Ollama/Qwen2.5-1.5b",
                        "office_verified": True,
                        "status": "success"
                    }
                    print("🤖 Локальный ИИ успешно сгенерировал аналитику текста!", flush=True)
                else:
                    raise Exception(f"Ollama вернул код ошибки: {response.status_code}")
                    
            except Exception as ai_error:
                print(f"❌ Ошибка вызова Ollama: {ai_error}", flush=True)
                ai_summary = f"Документ загружен, но локальный ИИ-анализатор вернул ошибку: {str(ai_error)}"
                extracted_data = {"status": "ai_error", "error": str(ai_error)}
        else:
            print(f"❌ Критическая ошибка: Файл не найден по пути {full_physical_path}", flush=True)
            ai_summary = f"Ошибка анализа файла '{title}'. Физический файл отсутствует в общем хранилище документов."
            extracted_data = {"status": "file_not_found"}

        # --- Запись результатов анализа напрямую в MySQL ---
        db = get_db_connection()
        cursor = db.cursor()
        
        sql = """
            UPDATE documents 
            SET status = 'completed', ai_summary = %s, ai_extracted_data = %s, updated_at = NOW() 
            WHERE id = %s
        """
        cursor.execute(sql, (ai_summary, json.dumps(extracted_data), doc_id))
        db.commit()
        
        cursor.close()
        db.close()
        
        print(f"✅ Анализ документа ID {doc_id} завершен. Результаты сохранены в MySQL!", flush=True)
        
    except Exception as e:
        print(f"❌ Ошибка в общем цикле воркера Python: {e}", flush=True)
        time.sleep(2) # Защита от бесконечного падения процессора при сбоях сети
