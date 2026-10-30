# Dzień 18 — Pełne testowanie biblioteki elektronicznej (P01–P08)

**Data:** 27.10.2026  
**Projekt:** Elektroniczna biblioteka firmowa (PHP + MySQL)  
**Baza robocza:** `biblioteka_online_test`  
**Osoba testująca:** [nie podano w pierwotnym raporcie]  
**Liczba przepracowanych godzin według notatek:** 7

> W oryginalnym dokumencie zadeklarowano „8/8 zaliczonych”, ale szczegółowe wyniki i dowody nie zostały wpisane, a pola kontrolne nie były zaznaczone. Poniżej zachowano ten stan, bez dopisywania wyników.

| Test | Zakres | Oczekiwany rezultat | Status w pierwotnym raporcie | Faktyczny wynik / dowód |
|---|---|---|---|---|
| P01 | Logowanie i wylogowanie | Poprawne hasło wpuszcza, błędne odrzuca; po wylogowaniu brak dostępu | oznaczono „sprawdzono” | nie wpisano |
| P02 | Katalog | Tylko widoczne materiały, prawidłowa karta dokumentu | oznaczono „sprawdzono” | nie wpisano |
| P03 | Wyszukiwanie | Tytuł, autor, kategoria, brak wyników, reset | oznaczono „sprawdzono” | nie wpisano |
| P04 | Pobieranie PDF | Plik działa; jedno pobranie dodaje jeden wpis, karta nie dodaje | oznaczono „sprawdzono” | nie wpisano |
| P05 | Materiały | Dodawanie, walidacja, wersjonowanie, ukrywanie i zachowanie historii | oznaczono „sprawdzono” | nie wpisano |
| P06 | Kategorie | Dodawanie, zmiana nazwy, blokada usuwania niepustej kategorii | oznaczono „sprawdzono” | nie wpisano |
| P07 | Historia pobrań | Dwaj pracownicy, filtrowanie i kolejność od najnowszych | oznaczono „sprawdzono” | nie wpisano |
| P08 | Uprawnienia | Brak pobierania bez logowania; pracownik nie może edytować ani oglądać historii | oznaczono „sprawdzono” | nie wpisano |

## Szczegółowa lista kontrolna — statusy do potwierdzenia

### P01. Logowanie
- [ ] `admin_demo`: logowanie prawidłowym hasłem.
- [ ] `employee1` i `employee2`: logowanie.
- [ ] Błędne hasło odrzucone.
- [ ] Po wylogowaniu brak dostępu do stron chronionych.

### P02. Katalog
- [ ] Katalog wyświetla wyłącznie `is_visible = 1`.
- [ ] Karta istniejącego materiału jest dostępna i zgodna z bazą.
- [ ] Ukryta karta jest niedostępna.

### P03. Wyszukiwanie
- [ ] Wyszukiwanie według tytułu i autora.
- [ ] Filtr kategorii działa z wyszukiwaniem.
- [ ] Nieistniejące hasło daje „Nie znaleziono żadnych materiałów”.
- [ ] Reset przywraca cały widoczny katalog.

### P04. Pobieranie PDF
- [ ] Właściwy PDF pobiera się i otwiera.
- [ ] Każde z dwóch pobrań tworzy dokładnie jeden nowy wpis.
- [ ] Otworzenie karty nie tworzy wpisu.
- [ ] W osobnej testowej kopii brak pliku nie tworzy wpisu; plik został przywrócony.

### P05. Materiały
- [ ] Administrator dodaje testowy dokument PDF.
- [ ] Puste obowiązkowe pola są odrzucane.
- [ ] Plik tekstowy i PDF powyżej 20 MB są odrzucane po stronie serwera.
- [ ] Wymiana PDF zwiększa wersję o 1.
- [ ] Dawne wpisy `downloads.file_version` zachowują starą wersję.
- [ ] Ukrytego dokumentu nie można zobaczyć ani pobrać przez bezpośredni adres.

### P06. Kategorie
- [ ] Utworzono i zmieniono nazwę kategorii testowej.
- [ ] Nie można usunąć kategorii z materiałami.
- [ ] Po przeniesieniu materiału można usunąć pustą kategorię.

### P07. Rejestr pobrań
- [ ] Pobrano pliki jako `employee1` i `employee2`.
- [ ] Filtry użytkownika i materiału działają osobno i razem.
- [ ] Najnowsze pobrania wyświetlają się na górze.

### P08. Uprawnienia
- [ ] Bez zalogowania bezpośredni adres `download.php?id=...` nie wydaje pliku.
- [ ] `employee1` nie ma dostępu do `admin/downloads.php`.
- [ ] `employee1` nie ma dostępu do `admin/edit_material.php?id=...`.
- [ ] W kopii testowej nieautoryzowana próba POST nie zmieniła danych.

## Zapytania SQL do weryfikacji

```sql
SELECT COUNT(*) AS total_downloads FROM downloads;

SELECT d.id, u.username, m.title, d.file_version, d.downloaded_at
FROM downloads d
JOIN users u ON u.id = d.user_id
JOIN materials m ON m.id = d.material_id
ORDER BY d.downloaded_at DESC, d.id DESC;

SELECT id, title, version, is_visible, file_path
FROM materials ORDER BY id;
```

## Rejestr błędów

| ID | Priorytet | Test | Sposób odtworzenia | Oczekiwano | Faktycznie | Plik | Zrzut ekranu | Status |
|---|---|---|---|---|---|---|---|---|
| BUG-001 | | | | | | | | |

## Podsumowanie z oryginalnych notatek

- Zgłoszono: **8/8 sprawdzone**, **0 błędów**.
- Uwaga: szczegółowe pola kontrolne i dowody pozostały niewypełnione. Nie wolno traktować samej deklaracji jako pełnego potwierdzenia testów.
