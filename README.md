# Elektroniczna biblioteka firmowa

**Aplikacja PHP + MySQL/MariaDB** do katalogowania i udostępniania pracownikom dokumentów PDF. Projekt przygotowany podczas praktyki zawodowej. Interfejs aplikacji jest w języku **polskim**.

## Funkcje

- Logowanie oraz wylogowanie: `login.php`, `logout.php`.
- Katalog widocznych dokumentów, wyszukiwanie po tytule i autorze, filtrowanie kategorii: `index.php`.
- Szczegóły dokumentu: `material.php?id=...`.
- Bezpieczne pobieranie z katalogu poza `htdocs`: `download.php`.
- Panel administratora: `admin/dashboard.php`.
- Zarządzanie materiałami, przesyłanie/zmiana dokumentu, numer wersji i ukrywanie: `admin/materials.php`, `admin/add_material.php`, `admin/edit_material.php`.
- Kategorie: `admin/categories.php`.
- Historia pobrań z filtrowaniem po pracowniku i materiale: `admin/downloads.php`.
- Przykładowa strona multimedialna: `admin/tutorial.php`.
- Walidacja formularza PDF w przeglądarce: JustValidate + `js/material-validation.js`; walidacja PHP pozostaje obowiązkowa.

**Role:** `admin`, `employee`. Samodzielna rejestracja nie jest przewidziana.

## 1. Wymagania

- Windows i XAMPP z Apache oraz PHP (w kodzie użyto funkcji PHP 8+, np. `str_starts_with`).
- MySQL lub MariaDB, rozszerzenia PHP: `pdo_mysql`, `fileinfo`, `mbstring`.
- Przeglądarka internetowa.
- Łączność z internetem przy korzystaniu z JustValidate z CDN (w przeciwnym razie walidacja PHP nadal działa).

## 2. Wgranie kodu

Skopiuj katalog projektu do folderu `htdocs` serwera Apache, np.:

```text
C:\xampp\htdocs\biblioteka-online projekt\
```

Uruchom Apache oraz MySQL w panelu XAMPP.

## 3. Baza danych

1. W phpMyAdmin (`http://localhost/phpmyadmin`) utwórz pustą bazę `biblioteka_online_test` z kodowaniem `utf8mb4`.
2. Zaimportuj plik `database/library.sql` **wyłącznie do pustej bazy demonstracyjnej**.
3. Plik `database/library.sql` jest historycznym zrzutem przykładowych danych (15.10.2026), a **nie** aktualną kopią rzeczywistej bazy po dniach 16–20.
4. Jeśli masz własną aktualną bazę danych, nie importuj zrzutu ponownie. Do przetłumaczenia wyłącznie pierwotnych rosyjskich przykładowych nazw i opisów możesz użyć `database/tlumaczenie_istniejacej_bazy_PL.sql` (instrukcja niżej).

**Uwaga:** historyczny zrzut zawiera demonstracyjne konta z hasłami zapisanymi jako hasze. Nie publikuj prawdziwych danych użytkowników.

## 4. Połączenie PHP z bazą

Skonfiguruj lokalny plik `config/database.php`: host, nazwę bazy `biblioteka_online_test`, użytkownika MySQL (`biblioteka_app`) i hasło. Przykład pliku bez prawdziwego hasła: `config/database.example.php`.

Użytkownik aplikacyjny powinien mieć niezbędne uprawnienia `SELECT`, `INSERT`, `UPDATE`, `DELETE` do odpowiedniej bazy; nie używaj konta `root` dla witryny. **Nie udostępniaj hasła MySQL w repozytorium publicznym.**

## 5. Prywatny katalog PDF

Utwórz folder:

```text
C:\xampp\biblioteka_private_pdfs
```

Musi znajdować się **poza `htdocs`**. Zadbaj, aby PHP mógł go odczytywać i zapisywać.

Pliki `admin/edit_material.php` i `download.php` używają tej lokalizacji. Formularz dodawania opiera się na katalogu nadrzędnym `DOCUMENT_ROOT` (standardowa instalacja XAMPP wskazuje tę samą ścieżkę). Przy innej instalacji sprawdź zgodność wszystkich ścieżek.

**Ważne:** w archiwum nie ma prawdziwych, prywatnych PDF-ów użytkownika. Przykładowy zrzut SQL zawiera nazwy `php_basics.pdf`, `php_web.pdf` itd., lecz same pliki nie są dołączone. Aby sprawdzić pobieranie, prześlij prawidłowy przykładowy PDF przez panel administratora lub przywróć pliki z autoryzowanej kopii.

## 6. Logowanie

Uruchom:

```text
http://localhost/biblioteka-online%20projekt/login.php
```

Przykładowe nazwy kont w zrzucie: `admin_demo`, `employee1`, `employee2`. Hasła pochodzą z przygotowanego wcześniej środowiska testowego; jeżeli zostały zmienione, użyj aktualnych danych dostępowych. Kont nie trzeba tworzyć ponownie po imporcie zrzutu, który już je zawiera.

Skrypt `js/scripts/create_demo_users.php` można uruchomić z terminala tylko dla pustej tabeli użytkowników; nie uruchamiaj go drugi raz na istniejących loginach.

## 7. Tłumaczenie istniejących rekordów MySQL

Tłumaczenie plików PHP **nie zmienia automatycznie** wartości zapisanych już w MySQL. Aby przetłumaczyć pierwotne rosyjskie dane demonstracyjne:

1. Wykonaj pełną kopię zapasową bazy i PDF.
2. Przejrzyj `database/tlumaczenie_istniejacej_bazy_PL.sql`.
3. Sprawdź, czy nie istnieją już polskie odpowiedniki nazw kategorii (mogłoby to naruszyć ograniczenie `UNIQUE`).
4. Uruchom skrypt **raz** w phpMyAdmin w bazie `biblioteka_online_test`. Skrypt aktualizuje tylko dokładnie dopasowane rosyjskie wartości, nie zmienia identyfikatorów, `file_path`, wersji dokumentów, historii pobrań, loginów ani haseł.
5. Własne materiały dodane później należy w razie potrzeby przetłumaczyć ręcznie w panelu administratora.

Nie zmieniaj nazwy bazy ani nazw tabel/kolumn (`users`, `materials`, `downloads`, `categories`): są wykorzystywane przez kod.

## 8. Sprawdzenie działania

Po uruchomieniu zweryfikuj:

1. Logowanie administratora i pracownika, błędne hasło, wylogowanie.
2. Katalog i wyszukiwanie, brak ukrytych dokumentów.
3. Dodawanie PDF (max 20 MB), edycję i ukrywanie z konta administratora.
4. Pobranie testowego PDF przez pracownika: dokładnie **jeden** nowy wpis w rejestrze na jedno pobranie.
5. Pobranie nowej wersji dokumentu i zachowanie numeru starej wersji we wcześniejszym wpisie.
6. Filtrowanie historii pobrań i odmowę dostępu do panelu administratora dla pracownika.

## 9. Struktura katalogów

```text
admin/                   panel administratora
config/                  sesje/autoryzacja i konfiguracja bazy
css/                     style
js/                      walidacja formularza, skrypty pomocnicze
images/                  obraz na stronie instrukcji
database/                demonstracyjny zrzut SQL + tłumaczenie danych
docs/                    raporty praktyki i opis tłumaczenia
login.php                logowanie
logout.php               wylogowanie
index.php                katalog i wyszukiwarka
material.php             karta materiału
download.php             zabezpieczone pobieranie
```

Niektóre starsze pliki-szkielety dotyczące wypożyczania książek pozostały z pierwotnego projektu; **nie są częścią aktualnej funkcji udostępniania PDF**.

## 10. Bezpieczeństwo i udostępnianie

W aplikacji zachowano weryfikację sesji i roli, tokeny CSRF, zapytania parametryzowane PDO oraz kodowanie wyświetlanego tekstu (`esc`). Nie publikuj pliku konfiguracyjnego z hasłem, zrzutów prywatnej bazy, zamkniętych plików PDF ani repozytoriów `.git` przypadkowo zagnieżdżonych w archiwum.

**Zakres weryfikacji niniejszej wersji:** sprawdzono składnię PHP/JavaScript i obecność plików. Działania aplikacji z prawdziwym MySQL i PDF trzeba sprawdzić na komputerze z XAMPP.
## ## 11. Konta demonstracyjne

Aplikacja „Elektroniczna biblioteka firmowa” nie posiada formularza samodzielnej rejestracji. Konta użytkowników są tworzone w bazie danych MySQL.

Aby uruchomić wersję demonstracyjną, należy zaimportować plik `biblioteka_online_test_create.sql` do MySQL.

### Dane logowania do wersji demonstracyjnej

| Rola | Login | Hasło |
| Administrator | `admin_demo` | `AdminTest2026!` |
| Pracownik 1 | `employee1` | `Employee1Test2026!` |
| Pracownik 2 | `employee2` | `Employee2Test2026!` |

### Uruchomienie aplikacji

1. Uruchom Apache i MySQL w XAMPP.
2. Zaimportuj bazę danych wraz z kontami demonstracyjnymi.
3. Skonfiguruj połączenie MySQL w pliku `config/database.php`.
4. Otwórz stronę `http://localhost/biblioteka-online%20projekt/login.php`.
5. Zaloguj się na wybrane konto demonstracyjne.

Administrator może zarządzać materiałami PDF, kategoriami i historią pobierania. Pracownicy mogą przeglądać katalog, wyszukiwać dokumenty i pobierać pliki PDF.

**Uwaga:** Podane konta i hasła służą wyłącznie do testowania aplikacji w środowisku lokalnym. Nie należy ich wykorzystywać w systemie produkcyjnym.