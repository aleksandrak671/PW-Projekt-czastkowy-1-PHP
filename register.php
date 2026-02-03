<?php
/**
 * Plik obsługujący proces rejestracji użytkownika
 * Tutaj waliduję wszystkie dane z formularza i zapisuję je do bazy
 */

// ładuję plik z konfiguracją połączenia do bazy
require_once 'config.php';

// sprawdzam czy formularz został wysłany metodą POST (bezpieczniejsza niż GET)
// jeśli ktoś spróbuje wejść bezpośrednio na ten plik, przekieruję go na formularz
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// tworzę pustą tablicę gdzie będę zbierać wszystkie błędy walidacji
$errors = [];

// pobieram dane z formularza używając $_POST
// trim() usuwa białe znaki z początku i końca
// ?? '' - jeśli pole nie istnieje, zwróć pusty string (zabezpieczenie przed błędami)
$imie = trim($_POST['imie'] ?? '');
$nazwisko = trim($_POST['nazwisko'] ?? '');
$login = trim($_POST['login'] ?? '');
$haslo = $_POST['haslo'] ?? '';                    // hasła NIE trimuję - spacje mogą być częścią hasła
$haslo_powtorz = $_POST['haslo_powtorz'] ?? '';
$email = trim($_POST['email'] ?? '');
$ulica = trim($_POST['ulica'] ?? '');
$numer_domu = trim($_POST['numer_domu'] ?? '');
$kod_pocztowy = trim($_POST['kod_pocztowy'] ?? '');
$miasto = trim($_POST['miasto'] ?? '');
$wyksztalcenie = $_POST['wyksztalcenie'] ?? '';
$zainteresowania = $_POST['zainteresowania'] ?? [];  // to będzie tablica z zaznaczonymi checkboxami


// walidacja danych - sprawdzam poprawność wszystkich pól:


// walidacja imienia
if (empty($imie)) {
    $errors[] = 'Imię jest wymagane.';
} elseif (strlen($imie) < 2) {
    $errors[] = 'Imię musi mieć minimum 2 znaki.';
} elseif (!preg_match('/^[a-zA-ZąćęłńóśźżĄĆĘŁŃÓŚŹŻ\s-]+$/u', $imie)) {
    // preg_match() sprawdza czy tekst pasuje do wzorca (regex)
    // Ten wzorzec akceptuje tylko litery (polskie i angielskie), spacje i myślnik
    $errors[] = 'Imię może zawierać tylko litery.';
}

// walidacja nazwiska - podobnie jak imię
if (empty($nazwisko)) {
    $errors[] = 'Nazwisko jest wymagane.';
} elseif (strlen($nazwisko) < 2) {
    $errors[] = 'Nazwisko musi mieć minimum 2 znaki.';
} elseif (!preg_match('/^[a-zA-ZąćęłńóśźżĄĆĘŁŃÓŚŹŻ\s-]+$/u', $nazwisko)) {
    $errors[] = 'Nazwisko może zawierać tylko litery.';
}

// walidacja loginu
if (empty($login)) {
    $errors[] = 'Login jest wymagany.';
} elseif (strlen($login) < 3) {
    $errors[] = 'Login musi mieć minimum 3 znaki.';
} elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $login)) {
    // Login może zawierać tylko litery, cyfry i podkreślnik
    $errors[] = 'Login może zawierać tylko litery, cyfry i znak podkreślenia.';
} else {
    // WAŻNE: sprawdzam czy login już nie istnieje w bazie (musi być unikalny)
    // używam prepared statement dla bezpieczeństwa (zabezpieczenie przed SQL injection)
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM uzytkownicy WHERE login = ?");
    $stmt->execute([$login]);
    if ($stmt->fetchColumn() > 0) {
        $errors[] = 'Podany login jest już zajęty. Wybierz inny.';
    }
}

// walidacja hasła
if (empty($haslo)) {
    $errors[] = 'Hasło jest wymagane.';
} elseif (strlen($haslo) < 6) {
    $errors[] = 'Hasło musi mieć minimum 6 znaków.';
}

// sprawdzam czy oba hasła są identyczne
if ($haslo !== $haslo_powtorz) {
    $errors[] = 'Hasła nie są identyczne.';
}

// walidacja e-maila
if (empty($email)) {
    $errors[] = 'Adres e-mail jest wymagany.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    // filter_var() z FILTER_VALIDATE_EMAIL sprawdza poprawność formatu emaila
    $errors[] = 'Podany adres e-mail jest nieprawidłowy.';
}

// walidacja pól adresowych - sprawdzam czy nie są puste
if (empty($ulica)) {
    $errors[] = 'Ulica jest wymagana.';
}

if (empty($numer_domu)) {
    $errors[] = 'Numer domu jest wymagany.';
}

if (empty($kod_pocztowy)) {
    $errors[] = 'Kod pocztowy jest wymagany.';
} elseif (!preg_match('/^\d{2}-\d{3}$/', $kod_pocztowy)) {
    // sprawdzam format kodu pocztowego: 2 cyfry, myślnik, 3 cyfry (np. 00-000)
    $errors[] = 'Kod pocztowy musi być w formacie XX-XXX.';
}

if (empty($miasto)) {
    $errors[] = 'Miasto jest wymagane.';
}

// walidacja wykształcenia - sprawdzam czy wybrano jedną z dozwolonych opcji
$dozwolone_wyksztalcenie = ['podstawowe', 'srednie', 'wyzsze'];
if (empty($wyksztalcenie)) {
    $errors[] = 'Wykształcenie jest wymagane.';
} elseif (!in_array($wyksztalcenie, $dozwolone_wyksztalcenie)) {
    // zabezpieczenie - ktoś mógł zmienić wartość w kodzie HTML
    $errors[] = 'Nieprawidłowa wartość wykształcenia.';
}

// walidacja zainteresowań
if (empty($zainteresowania) || !is_array($zainteresowania)) {
    $errors[] = 'Wybierz przynajmniej jedno zainteresowanie.';
} else {
    // dodatkowe bezpieczeństwo - sprawdzam czy wybrane IDs rzeczywiście istnieją w bazie
    $placeholders = str_repeat('?,', count($zainteresowania) - 1) . '?';  // tworzę ?,?,? dla prepared statement
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM zainteresowania WHERE id IN ($placeholders)");
    $stmt->execute($zainteresowania);
    if ($stmt->fetchColumn() != count($zainteresowania)) {
        $errors[] = 'Wybrano nieprawidłowe zainteresowania.';
    }
}

// wyświetlenie wyniku - HTML:

?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wynik rejestracji</title>
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="container">
        <?php if (!empty($errors)): ?>
            <!-- jeśli są jakieś błędy walidacji, pokazuję je użytkownikowi -->
            <h1>Błąd rejestracji</h1>
            <div class="error">
                <strong>Wystąpiły następujące błędy:</strong>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <a href="javascript:history.back()" class="back-link">← Wróć do formularza</a>
        
        <?php else: ?>
            <?php
            // brak błędów - zapisuję dane do bazy!
            try {
                // rozpoczynam transakcję - jeśli coś pójdzie nie tak, wszystko zostanie cofnięte
                $pdo->beginTransaction();
                
                // hashuję hasło używając password_hash() - NIGDY nie zapisuję hasła w czystej formie!
                // PASSWORD_DEFAULT używa obecnie bcrypt (bezpieczny algorytm)
                $haslo_hash = password_hash($haslo, PASSWORD_DEFAULT);
                
                // wstawiam dane użytkownika do tabeli uzytkownicy
                // używam prepared statement (? to placeholdery) dla bezpieczeństwa
                $stmt = $pdo->prepare("
                    INSERT INTO uzytkownicy 
                    (imie, nazwisko, login, haslo, email, ulica, numer_domu, kod_pocztowy, miasto, wyksztalcenie) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $imie,
                    $nazwisko,
                    $login,
                    $haslo_hash,      // zapisuję zahashowane hasło!
                    $email,
                    $ulica,
                    $numer_domu,
                    $kod_pocztowy,
                    $miasto,
                    $wyksztalcenie
                ]);
                
                // pobieram ID nowo utworzonego użytkownika (auto_increment)
                $uzytkownik_id = $pdo->lastInsertId();
                
                // teraz zapisuję zainteresowania użytkownika do tabeli łączącej
                $stmt = $pdo->prepare("
                    INSERT INTO uzytkownicy_zainteresowania (uzytkownik_id, zainteresowanie_id) 
                    VALUES (?, ?)
                ");
                // dla każdego wybranego zainteresowania tworzę wpis w tabeli
                foreach ($zainteresowania as $zainteresowanie_id) {
                    $stmt->execute([$uzytkownik_id, $zainteresowanie_id]);
                }
                
                // wszystko się udało - zatwierdzam transakcję
                $pdo->commit();
                
                // pobieram nazwy zainteresowań do wyświetlenia (zamiast samych ID)
                $placeholders = str_repeat('?,', count($zainteresowania) - 1) . '?';
                $stmt = $pdo->prepare("SELECT nazwa FROM zainteresowania WHERE id IN ($placeholders)");
                $stmt->execute($zainteresowania);
                $nazwy_zainteresowan = $stmt->fetchAll(PDO::FETCH_COLUMN);
                
                // przygotowuję czytelne nazwy wykształcenia do wyświetlenia
                $wyksztalcenie_nazwy = [
                    'podstawowe' => 'Podstawowe',
                    'srednie' => 'Średnie',
                    'wyzsze' => 'Wyższe'
                ];
            ?>
            
            <h1>Rejestracja zakończona pomyślnie!</h1>
            <div class="success">
                <strong>Gratulacje!</strong> Twoje konto zostało utworzone.
            </div>
            
            <!-- wyświetlam wszystkie zapisane dane jako potwierdzenie -->
            <div class="user-data">
                <h2>Twoje dane:</h2>
                <table>
                    <tr>
                        <th>Imię:</th>
                        <td><?php echo htmlspecialchars($imie); ?></td>
                    </tr>
                    <tr>
                        <th>Nazwisko:</th>
                        <td><?php echo htmlspecialchars($nazwisko); ?></td>
                    </tr>
                    <tr>
                        <th>Login:</th>
                        <td><?php echo htmlspecialchars($login); ?></td>
                    </tr>
                    <tr>
                        <th>E-mail:</th>
                        <td><?php echo htmlspecialchars($email); ?></td>
                    </tr>
                    <tr>
                        <th>Adres:</th>
                        <td>
                            <?php echo htmlspecialchars($ulica . ' ' . $numer_domu); ?><br>
                            <?php echo htmlspecialchars($kod_pocztowy . ' ' . $miasto); ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Wykształcenie:</th>
                        <td><?php echo htmlspecialchars($wyksztalcenie_nazwy[$wyksztalcenie]); ?></td>
                    </tr>
                    <tr>
                        <th>Zainteresowania:</th>
                        <td><?php echo htmlspecialchars(implode(', ', $nazwy_zainteresowan)); ?></td>
                    </tr>
                    <tr>
                        <th>Data rejestracji:</th>
                        <td><?php echo date('d.m.Y H:i:s'); ?></td>
                    </tr>
                </table>
            </div>
            
            <a href="index.php" class="back-link">← Zarejestruj kolejnego użytkownika</a>
            
            <?php
            } catch (PDOException $e) {
                // jeśli wystąpił błąd podczas zapisu do bazy, cofam transakcję
                $pdo->rollBack();
            ?>
            <h1>Błąd zapisu</h1>
            <div class="error">
                <strong>Wystąpił błąd podczas zapisu do bazy danych:</strong>
                <p><?php echo htmlspecialchars($e->getMessage()); ?></p>
            </div>
            <a href="javascript:history.back()" class="back-link">← Wróć do formularza</a>
            <?php
            }
            ?>
        <?php endif; ?>
    </div>
</body>
</html>
