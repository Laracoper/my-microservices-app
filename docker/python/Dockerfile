FROM python:3.11-slim

WORKDIR /app

# Копируем файл зависимостей
COPY src/python-service/requirements.txt ./

# Скачиваем библиотеки через полный эндпоинт Яндекса
RUN pip install --no-cache-dir --trusted-host mirror.yandex.ru -i https://mirrors.aliyun.com/pypi/simple/ -r requirements.txt

CMD ["python", "main.py"]
