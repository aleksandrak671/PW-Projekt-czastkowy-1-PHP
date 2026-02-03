<?php
/**
 * plik konfiguracyjny do projektu rejestracji uzytkownikow
 * 
 * INSTRUKCJA:
 * 1. skopiuj ten plik jako config.php
 * 2. uzupelnij dane dostepowe do swojej bazy danych
 */

// dane dostepowe do bazy danych
define('DB_HOST', 'localhost');              // adres serwera mysql
define('DB_NAME', 'projekt1_rejestracja');   // nazwa bazy danych
define('DB_USER', 'twoj_uzytkownik');        // nazwa uzytkownika mysql
define('DB_PASS', 'twoje_haslo');            // haslo do mysql

// polaczenie z baza danych uzywajac pdo
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die("Błąd połączenia z bazą danych: " . $e->getMessage());
}
