# Task API

Простой REST API для управления задачами (Symfony + Doctrine + PostgreSQL).

Этот README описывает как запустить проект локально в Docker, как протестировать API (curl / Swagger / Postman), и содержит примеры запросов и ожидаемых ответов. Также есть краткое техническое описание архитектуры.

---

## Быстрый старт (Docker)

Требования: Docker, Docker Compose.

1. Клонировать репозиторий:

   git clone https://github.com/Mart22052006/tasks-CRUD.git
   cd task-api

2. Собрать и запустить контейнеры:

   docker compose build --no-cache

   После запуска сервисов приложение будет доступно по: http://localhost:8000

3. Генерация JWT ключей (если их нет):

   Если в `config/jwt/` нет ключей, сгенерируйте их локально и добавьте в образ/контейнер или в папку проекта:

   openssl genrsa -out config/jwt/private.pem -aes256 4096
   openssl rsa -in config/jwt/private.pem -out config/jwt/public.pem
   chmod 600 config/jwt/private.pem

---

## Проверка состояния

- Просмотреть статус контейнеров:
  docker compose ps
- Логи приложения:
  docker compose logs -f app
- Просмотреть логи Symfony:
  docker compose exec app tail -n 200 var/log/dev.log

---

## Документация (Swagger / OpenAPI)

Настроен `NelmioApiDocBundle`, UI будет доступен по:

  http://localhost:8000/api/docs

В нём можно выполнять запросы прямо из браузера. Нажмите "Authorize" и вставьте `Bearer <TOKEN>` для авторизации.

---

## Авторизация (JWT)

1. Зарегистрироваться:

   POST /api/register
   Content-Type: application/json
   Body: {"email":"user@example.test","password":"secret123"}

   Ответ: 201 Created (или 409 Conflict если email уже занят)

2. Получить токен (login):

   POST /api/login_check
   Content-Type: application/json
   Body: {"username":"user@example.test","password":"secret123"}

   Ответ: {"token": "eyJ..."}

3. Использовать токен в заголовке Authorization:

   Authorization: Bearer <token>

---

## Примеры запросов (curl) и ожидаемые ответы

1) Создать задачу

curl -i -X POST http://localhost:8000/api/tasks \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"title":"New task","description":"Description","status":"todo"}'

Ожидаемый ответ: 201 Created
Body (JSON): task объект (groups: task:read)

2) Получить список задач (пагинация и фильтр):

curl -H "Authorization: Bearer $TOKEN" "http://localhost:8000/api/tasks?page=1&limit=10&status=todo"

Ожидаемый ответ: 200 OK
Body: {"items": [...], "meta": {"total":..., "page":1, "limit":10, "totalPages":...}}

3) Получить задачу по ID

GET /api/tasks/{id}
Ответ: 200 OK или 404 Not Found

4) Обновить задачу

PUT /api/tasks/{id}
Body: {"title":"Updated"}
Ответ: 200 OK

5) Удалить задачу

DELETE /api/tasks/{id}
Ответ: 204 No Content

---

## Как назначить роль ADMIN (быстро)

Через psql в контейнере БД:

  docker compose exec database psql -U taskuser -d taskdb -c "UPDATE app_user SET roles='[\"ROLE_ADMIN\"]' WHERE email='user@example.test';"

Или через SQL-консоль Doctrine:

  docker compose exec app php bin/console doctrine:query:sql "UPDATE app_user SET roles='[\"ROLE_ADMIN\"]' WHERE email='user@example.test';"

---

## Тестирование

Запуск phpunit в контейнере:

  docker compose exec app ./vendor/bin/phpunit --testdox

Примечание: функциональные тесты могут требовать отдельной test-базы/фикстур.

---

## Postman коллекция

Файл `postman_collection.json` есть в корне проекта — импортируйте её в Postman/Insomnia. Коллекция содержит примеры для регистрации, логина, CRUD задач.

---

## Архитектура и описание проекта

Кратко:
- Symfony 6+ приложение.
- Слой контроллеров (`src/Controller/Api`) — принимают HTTP, денормализуют DTO, валидируют и передают в сервисы.
- DTO (`src/DTO`) — объекты для передачи данных между слоями; валидируются Symfony Validator.
- Сервисы (`src/Service`) — бизнес-логика (UserService, TaskService, UserQueryService, Mapper-ы).
- Репозитории (`src/Repository`) — доступ к БД через Doctrine ORM.
- Entities (`src/Entity`) — `User`, `Task`.
- Mappers (`src/Service/Mapper`) — преобразование Entity ↔ DTO.
- API документация — NelmioApiDocBundle (Swagger UI).

Dependency injection и автоконфигурация настроены через `config/services.yaml`.

---
