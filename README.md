# Системные требования
    PHP >=8.2
    PHP расширения: Ctype, iconv, PCRE, Session, SimpleXML, Tokenizer
    Mysql 8.0.*
    Composer
    Symfony CLI

# Установка
Необходимо клонировать репозиторий `git clone https://github.com/HighBornHoney/test-project.git`

Перейти в папку с проектом `cd test-project`

Установить зависимости `composer install`

Указать данные для соединения(включая версию) с mysql в переменной окружения DATABASE_URL, которая находится в файле .env. Или создать файл .env.local и переопределить эту переменную в нем.

Выполнить миграции
`php bin/console doctrine:migrations:migrate`

Запустить веб сервер Symfony
`symfony server:start`

Заполнить базу данных тестовыми данными
`php bin/console db:seed`

# Запросы

## Get list of Books

### Request

`GET /books`

### Response

    Status: 200 OK
    Content-Type: application/json

    {"id": 3, "title": "PHP 8", "year": "2023", "authors": [{"surname": "Симдянов"},{"surname": "Котеров"}],"publisher": {"title": "Scholastic"}}

## Create a Book

### Request

`POST /books`

    {"title":"Awesome book","year":"2024","author_ids":[1],"publisher_id":1}

### Response

    Status: 201 Created
    Content-Type: application/json

    {"book_id":4}

## Delete a Book

### Request

`DELETE /books/id`

### Response

    Status: 200 OK
    Content-Type: application/json

    {"success":1}

## Create an Author

### Request

`POST /authors`

    {"name": "George", "surname": "Orwell"}

### Response

    Status: 201 Created
    Content-Type: application/json

    {"author_id":2}

## Delete an Author

### Request

`DELETE /authors/id`

### Response

    Status: 200 OK
    Content-Type: application/json

    {"success":1}

## Update a Publisher

### Request

`PUT /publishers/id`

    {"title": "Scholastic", "address": "New York, New York, United States"}

### Response

    Status: 200 OK
    Content-Type: application/json

    {"success":1}

## Delete a Publisher

### Request

`DELETE /publishers/id`

### Response

    Status: 200 OK
    Content-Type: application/json

    {"success":1}
    
# Консольные команды

`php bin/console db:seed` Добавить в базу данных тестовые данные

`php bin/console delete:authors-without-books` Удалить из базы данных тех авторов, у которых нет книг

# Дополнительные настройки

С помощью переменной окружения `FORMAT` можно указать формат обмена сообщениями, json или xml

Также можно указать токен авторизации в переменной `AUTH_TOKEN`, после чего выполнение запросов без авторизации станет невозможным.

Для того, чтобы авторизоваться, необходимо передать в GET параметре `token` значение, установленное в переменной окружения `AUTH_TOKEN`.

Например, `DELETE /publishers/id?token=your_token`
