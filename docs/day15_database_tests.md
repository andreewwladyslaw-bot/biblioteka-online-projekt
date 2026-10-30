# Dzień 15 — Eksport, import i uprawnienia MySQL

Data: 24.10.2026

## Baza źródłowa
biblioteka_online_test

## Baza testowa
biblioteka_online_restore_test

## Eksport
Nazwa pliku SQL: biblioteka_day15.sql
Wynik: pomyślnie

## Import
Wynik: pomyślnie

## Weryfikacja tabel

| Tabela | Baza źródłowa | Baza odtworzona |
|---|---|---|
| users | 3 | 3 |
| categories | 4 | 4 |
| materials | 5 | 5 |
| downloads | 2 | 2 |

## Weryfikacja uprawnień
Użytkownik: biblioteka_practice

SELECT: pomyślnie  
CREATE TABLE: odmowa dostępu

## Wniosek
Wszystko działa poprawnie.
