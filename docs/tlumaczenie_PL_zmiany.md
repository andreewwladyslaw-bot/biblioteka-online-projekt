# Zmiana języka projektu na polski

**Podstawa:** archiwum `biblioteka-online projekt(3).zip` przekazane przez autora projektu.  
**Data przygotowania:** 29.10.2026.

## Co przetłumaczono

- Strony użytkownika: logowanie, katalog, wyszukiwarka, karta dokumentu i komunikaty pobierania PDF.
- Panel administratora: materiały, kategorie, rejestr pobrań, edycja, formularze, instrukcja.
- Komunikaty błędów po stronie PHP i walidacji JavaScript.
- Komentarze w kodzie PHP/CSS i komunikaty terminalowego narzędzia tworzenia kont.
- Przykładowe polskie wartości w `database/library.sql` (z zachowaniem identyfikatorów, kluczy, loginów, ról i haszy haseł).
- Raporty praktyki i dokumentację użytkownika.
- W atrybutach HTML ustawiono `lang="pl"`.

## Co celowo pozostało bez zmian

- Nazwy plików i adresy URL (`login.php`, `download.php`, ...).
- Zmienne i identyfikatory w kodzie, nazwy tabel oraz kolumn SQL.
- Loginy użytkowników, role `admin`/`employee`, hasze haseł.
- Ścieżka do prywatnego katalogu PDF i nazwy fizycznych plików.
- Zrzuty ekranu w `docs/day17_cms/` i historyczne archiwum `docs/day17_cms.zip` — to dowody wyglądu poprzedniej wersji aplikacji, a nie aktualne elementy interfejsu.
- Ilustracja `images/library_instruction.png` zawiera podpisy w języku angielskim. Zachowano ją bez ingerencji w obraz.
- Dane już istniejące w lokalnej bazie nie zmieniają się automatycznie po podmianie plików PHP. W tym celu dołączono skrypt migracji przykładowych danych.

## Ważne wskazówki

1. **Zrób kopię starego projektu i MySQL.**
2. Podmień pliki PHP/JS z tego pakietu i nie nadpisuj działającej bazy historycznym plikiem SQL.
3. Aby przetłumaczyć starsze, rosyjskie przykłady w bazie, przeczytaj `README.md` i uruchom opcjonalnie `database/tlumaczenie_istniejacej_bazy_PL.sql` po sprawdzeniu konfliktów nazw kategorii.
4. Materiały dodane ręcznie po przygotowaniu zrzutu przetłumacz z panelu administratora.
5. Sprawdź polskie komunikaty i działanie pobierania, wyszukiwania oraz formularzy na swoim komputerze.

## Weryfikacja

- Przeprowadzono kontrolę składni PHP (narzędzie `php -l`).
- Przeprowadzono sprawdzenie składni JavaScript (narzędzie `node --check`).
- Nie testowano połączenia z lokalnym MySQL autora ani faktycznego pobierania jego plików PDF.
