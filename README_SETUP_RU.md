# Как установить исправленную версию (День 19)

Этот архив подготовлен из переданного `biblioteka-vlad-safe.zip`.
В нём **нет папки `.git`** и **нет рабочего пароля MySQL**.
Код и документация исправлены, но реальную работу с вашей MySQL и PDF-файлами
надо проверить локально в XAMPP. Не удаляйте свой старый репозиторий и базу.

## 1. Сохраните резервную копию

Скопируйте действующую папку проекта в безопасное место. Отдельно сохраните
`config/database.php` из старой версии, поскольку там находится действующий пароль.
Убедитесь, что резервная копия MySQL и `C:\xampp\biblioteka_private_pdfs` существует.

## 2. Установите проект

Если репозиторий находится в `C:\Projects\biblioteka-vlad-safe`, распакуйте
содержимое архива поверх файлов **именно этой папки проекта**.
Учтите: стандартный Apache/XAMPP обслуживает сайт из `C:\xampp\htdocs`,
поэтому для локального сайта либо разместите Git-репозиторий сразу в
`C:\xampp\htdocs\biblioteka-vlad-safe`, либо настройте Apache Alias/VirtualHost
на путь в `C:\Projects`. Простое клонирование в `C:\Projects` не делает сайт
доступным через `http://localhost/biblioteka-vlad-safe/`.
Если используете существующий репозиторий Git, **не удаляйте его `.git`**.
Не копируйте обратно `.git` из старой папки пользователя.

## 3. Настройте пароль MySQL (обязательный шаг)

В новой версии пароль **не хранится** в `config/database.php`.

1. Скопируйте `config/database.local.example.php` в файл `config/database.local.php`.
2. В новом файле найдите `password` и вставьте пароль пользователя `biblioteka_app`
   из **своей старой конфигурации**. Не публикуйте пароль в чате и GitHub.
3. Проверьте базу `biblioteka_online_test` и имя пользователя `biblioteka_app`.
4. `.gitignore` исключает файл `config/database.local.php`.

Вместо файла можно установить переменные окружения
`BIBLIOTEKA_DB_HOST`, `BIBLIOTEKA_DB_NAME`, `BIBLIOTEKA_DB_USER`,
`BIBLIOTEKA_DB_PASSWORD` (например, в Apache/XAMPP).

## 4. PDF-файлы

Папка PDF теперь определяется одинаково в `admin/add_material.php`,
`admin/edit_material.php` и `download.php`: соседняя с `htdocs`
`biblioteka_private_pdfs`. В стандартном XAMPP это:
`C:\xampp\biblioteka_private_pdfs`.

Если ваш XAMPP установлен в другом месте, можно указать переменную окружения
`BIBLIOTEKA_PDF_DIR`. Папка должна существовать и быть доступна Apache для чтения,
а для добавления и редактирования — для записи. PDF должны находиться вне `htdocs`.

В `database/library.sql` есть демонстрационные имена `php_basics.pdf` и другие.
SQL-дамп **не содержит эти PDF-файлы**. Если физически их нет в закрытой папке,
скачивание таких примеров по-прежнему будет возвращать 404.
Для проверки добавьте новый настоящий PDF через административную панель.

Для лимита загрузки до 20 МБ проверьте в `php.ini` (для XAMPP):
`upload_max_filesize = 20M`, `post_max_size = 25M`. Затем перезапустите Apache.

## 5. Проверьте сайт

Откройте `http://localhost/<папка-сайта>/login.php`.
Проверьте вход, каталог, поиск, загрузку учебного PDF, одну запись на скачивание,
редактирование версии, скрытие и 403 для сотрудника на административных страницах.
Сохранённые ранее PDF и рабочие данные удалять не требуется.

Для журнала MySQL выполните:

```sql
SELECT COUNT(*) AS total_downloads FROM downloads;
```

Скачайте один PDF, повторите запрос — количество должно увеличиться на 1.

## 6. Исправление Git (делать в ПРАВИЛЬНОМ репозитории)

```powershell
git rev-parse --show-toplevel
git status
```

Путь должен быть папкой **самого проекта** (например,
`C:/xampp/htdocs/biblioteka-vlad-safe` или `C:/Projects/biblioteka-vlad-safe`),
а не `C:/Users/<пользователь>`.

В загруженном архиве пустая папка `biblioteka-online-projekt` была
записана в Git как вложенный репозиторий (gitlink, mode 160000).
Если она **не содержит нужный код** и больше не используется, уберите именно
её ссылку из индекса (в папке нужного репозитория):

```powershell
git rm --cached biblioteka-online-projekt
```

**Не удаляйте чужие каталоги, не используйте `git clean -fdx` / `git push --force`.**
Перед коммитом просмотрите изменённые файлы и исключите секреты.

```powershell
git status
git diff --check
git add README.md.txt README_SETUP_RU.md .gitignore config/database.php config/database.local.example.php config/storage.php login.php material.php download.php admin/dashboard.php admin/add_material.php admin/edit_material.php admin/tutorial.php docs/day19_bugfix_report.md images/library_instruction.png docs/day17_cms/
git diff --cached --name-only
git commit -m "Day 19: fix library bugs and configuration"
git push -u origin Vlad
```

При ошибке `fetch first` сначала синхронизируйте ветки без `--force`.
Перед публикацией репозитория смените старый пароль MySQL, если он ранее
попал в коммиты: изменение рабочего файла не удаляет пароль из истории Git.

## 7. Ограничения проверки

В этой среде был доступен PHP CLI, но не работающая локальная база XAMPP,
поэтому выполнена проверка синтаксиса PHP и JavaScript, структуры
и соответствия путей. Функциональное тестирование с MySQL и реальными PDF
нужно провести на вашей машине и записать результаты в отчёт.
