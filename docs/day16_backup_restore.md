<CodeBlock language="markdown" editable>
# День 16 — Резервное копирование и восстановление


Дата: 25.10.2026
1. Резервная копия MySQL
Исходная база: biblioteka_online_test
Файл: biblioteka_day16.sql
Экспорт: успешно 
2. Резервная копия PDF
Исходная папка: C:\xampp\biblioteka_private_pdfs

Количество исходных PDF: 3
Количество скопированных PDF: 3
Проверка SHA-256: успешно 
3. Восстановление MySQL
Тестовая база: biblioteka_day16_restore
Импорт: успешно
Таблица	До экспорта	После восстановления
users	
categories	
materials	
downloads	
4. Восстановление PDF
Тестовая папка: restore_test_pdfs
Количество восстановленных PDF: 3
Контрольные суммы: совпадают 
5. Проверка связей
Все ли file_path указывают на существующие PDF?
да 
6. Итог
создал новую базу даных biblioteka_day16_restore.Совпадают все файлы в папках pdfs и restore_test_pdfs
</CodeBlock>