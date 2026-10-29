# Dzień 16 — Kopia zapasowa i odtwarzanie danych

**Data:** 25.10.2026

## 1. Kopia zapasowa MySQL

- Oryginalna baza: `biblioteka_online_test`.
- Plik: `biblioteka_day16.sql`.
- Wynik zapisany w notatkach: eksport **udany**.

## 2. Kopia zapasowa dokumentów PDF

- Folder źródłowy: `C:\xampp\biblioteka_private_pdfs`.
- Liczba oryginalnych PDF: **3**.
- Liczba skopiowanych PDF: **3**.
- Kontrola SHA-256: według notatek **udana**.

## 3. Odtwarzanie bazy w środowisku testowym

- Baza docelowa: `biblioteka_day16_restore`.
- Wynik zapisany w notatkach: import **udany**.

| Tabela | Liczba przed eksportem | Liczba po odtworzeniu |
|---|---:|---:|
| `users` | do uzupełnienia | do uzupełnienia |
| `categories` | do uzupełnienia | do uzupełnienia |
| `materials` | do uzupełnienia | do uzupełnienia |
| `downloads` | do uzupełnienia | do uzupełnienia |

## 4. Odtwarzanie plików PDF

- Folder testowy: `restore_test_pdfs`.
- Według notatek: odtworzono **3** pliki PDF.
- Sumy kontrolne: **zgodne** według notatek.
- Powiązanie `file_path` z plikami: oznaczone jako **tak** w pierwotnym raporcie.

## 5. Wniosek

Utworzono bazę `biblioteka_day16_restore`. W pierwotnym raporcie stwierdzono zgodność plików w katalogach `pdfs` i `restore_test_pdfs`. Brak wpisanych liczników rekordów tabel — należy uzupełnić je na podstawie rzeczywistych danych, bez zgadywania.
