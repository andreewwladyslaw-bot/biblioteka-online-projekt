<?php
/**
 * PDF are stored outside Apache DocumentRoot; use one location for all actions.
 * Override with BIBLIOTEKA_PDF_DIR only when the XAMPP installation differs.
 */
function pdfStorageDir(): string
{
    $custom = getenv('BIBLIOTEKA_PDF_DIR');
    if (is_string($custom) && trim($custom) !== '') {
        return rtrim($custom, '/\\');
    }

    $documentRoot = realpath((string) ($_SERVER['DOCUMENT_ROOT'] ?? ''));
    if ($documentRoot === false) {
        throw new RuntimeException('Не удалось определить DocumentRoot веб-сервера.');
    }

    return dirname($documentRoot) . DIRECTORY_SEPARATOR . 'biblioteka_private_pdfs';
}
