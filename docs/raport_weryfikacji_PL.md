# Raport techniczny — wersja polskojęzyczna

**Data:** 29.10.2026

Wykonano kontrolę pakietu kodu po tłumaczeniu:

- **25 plików PHP:** składnia sprawdzona za pomocą `php -l`, brak błędów.
- **JavaScript:** składnia pliku `js/material-validation.js` sprawdzona przez `node --check`, brak błędów.
- We wszystkich aktywnych szablonach HTML ustawiono `lang="pl"`.
- Nie pozostały rosyjskie napisy w kodzie PHP, JS, CSS, demonstracyjnym SQL i raportach tekstowych. Wyjątkiem jest celowy skrypt migracji zawierający oryginalne rosyjskie wartości w warunkach `WHERE`.
- Hasze haseł demonstracyjnych kont oraz nazwy tabel/kolumn SQL zachowano bez zmian.
- Nie dołączono zagnieżdżonych katalogów `.git`, które występowały w przekazanym archiwum.

**Nie wykonano testów uruchomieniowych w środowisku autora:** nie było dostępu do jego lokalnej bazy MySQL i prywatnych plików PDF. Testy logowania, przesyłania PDF, pobierania, uprawnień i rejestru należy wykonać w XAMPP zgodnie z `README.md`.
