<!DOCTYPE html>
<html lang="pl">
<head>
    <!-- ustawiam kodowanie UTF-8 żeby polskie znaki działały poprawnie -->
    <meta charset="UTF-8">
    <!-- dodaję viewport dla responsywności na urządzeniach mobilnych -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rejestracja użytkownika</title>
    <!-- podłączam mój plik CSS ze stylami -->
    <link rel="stylesheet" href="style.css?v=2">
</head>
<body>
    <div class="container">
        <h1>Rejestracja użytkownika</h1>
        
        <!-- tworzę formularz który wysyła dane do register.php metodą POST
             dodałam novalidate żeby wyłączyć wbudowaną walidację przeglądarki - 
             wolę walidować wszystko po stronie serwera w PHP dla większego bezpieczeństwa -->
        <form action="register.php" method="POST" novalidate>
            
            <!-- sekcja z danymi osobowymi - ustawiłam imię i nazwisko obok siebie używając flex -->
            <div class="form-row">
                <div class="form-group">
                    <label for="imie" class="required">Imię</label>
                    <!-- pole wymagane (required) + placeholder jako podpowiedź dla użytkownika -->
                    <input type="text" id="imie" name="imie" required 
                           placeholder="Podaj imię">
                </div>
                <div class="form-group">
                    <label for="nazwisko" class="required">Nazwisko</label>
                    <input type="text" id="nazwisko" name="nazwisko" required 
                           placeholder="Podaj nazwisko">
                </div>
            </div>

            <!-- dane do logowania - tutaj użytkownik wybiera swój login i hasło -->
            <div class="form-group">
                <label for="login" class="required">Login</label>
                <input type="text" id="login" name="login" required 
                       placeholder="Wybierz unikalny login">
                <small>Login musi mieć minimum 3 znaki</small>
            </div>

            <div class="form-group">
                <label for="haslo" class="required">Hasło</label>
                <!-- używam type="password" żeby hasło było ukryte gwiazdkami -->
                <input type="password" id="haslo" name="haslo" required 
                       placeholder="Podaj hasło">
                <small>Hasło musi mieć minimum 6 znaków</small>
            </div>

            <div class="form-group">
                <label for="haslo_powtorz" class="required">Powtórz hasło</label>
                <!-- sprawdzam potem czy oba hasła są identyczne -->
                <input type="password" id="haslo_powtorz" name="haslo_powtorz" required 
                       placeholder="Powtórz hasło">
            </div>

            <div class="form-group">
                <label for="email" class="required">Adres e-mail</label>
                <!-- type="email" daje podstawową walidację formatu emaila -->
                <input type="email" id="email" name="email" required 
                       placeholder="np. jan.kowalski@example.com">
            </div>

            <!-- nagłówek oddzielający sekcję adresu -->
            <h2 style="margin: 30px 0 20px; font-size: 18px; border-bottom: 1px solid #ddd; padding-bottom: 10px;">
                Adres zamieszkania
            </h2>

            <!-- pola adresowe - ulica zajmuje więcej miejsca niż numer domu -->
            <div class="form-row">
                <div class="form-group" style="flex: 2;">
                    <label for="ulica" class="required">Ulica</label>
                    <input type="text" id="ulica" name="ulica" required 
                           placeholder="Nazwa ulicy">
                </div>
                <div class="form-group" style="flex: 1;">
                    <label for="numer_domu" class="required">Nr domu/mieszkania</label>
                    <input type="text" id="numer_domu" name="numer_domu" required 
                           placeholder="np. 10/5">
                </div>
            </div>

            <!-- kod pocztowy i miasto w jednym wierszu -->
            <div class="form-row">
                <div class="form-group" style="flex: 1;">
                    <label for="kod_pocztowy" class="required">Kod pocztowy</label>
                    <input type="text" id="kod_pocztowy" name="kod_pocztowy" required 
                           placeholder="00-000">
                </div>
                <div class="form-group" style="flex: 2;">
                    <label for="miasto" class="required">Miasto</label>
                    <input type="text" id="miasto" name="miasto" required 
                           placeholder="Nazwa miasta">
                </div>
            </div>

            <!-- lista rozwijana do wyboru wykształcenia (select) -->
            <div class="form-group">
                <label for="wyksztalcenie" class="required">Wykształcenie</label>
                <select id="wyksztalcenie" name="wyksztalcenie" required>
                    <option value="">-- Wybierz wykształcenie --</option>
                    <option value="podstawowe">Podstawowe</option>
                    <option value="srednie">Średnie</option>
                    <option value="wyzsze">Wyższe</option>
                </select>
            </div>

            <!-- zainteresowania - checkboxy generowane dynamicznie z bazy danych -->
            <div class="form-group">
                <label class="required">Zainteresowania</label>
                <small style="display: block; margin-bottom: 15px; color: #666;">Wybierz przynajmniej jedno zainteresowanie</small>
                
                <?php
                // pobieram zainteresowania z bazy - require_once ładuje plik config.php tylko raz
                require_once 'config.php';
                
                try {
                    // wykonuję zapytanie SQL aby pobrać wszystkie zainteresowania posortowane alfabetycznie
                    $stmt = $pdo->query("SELECT id, nazwa FROM zainteresowania ORDER BY nazwa");
                    $zainteresowania = $stmt->fetchAll();  // pobieram wszystkie wyniki jako tablicę
                    
                    // generuję ładne karty dla każdego zainteresowania
                    echo '<div class="interests-grid">';
                    foreach ($zainteresowania as $zainteresowanie) {
                        echo '<label class="interest-card">';
                        echo '<input type="checkbox" name="zainteresowania[]" value="' . $zainteresowanie['id'] . '">';
                        echo '<span class="interest-name">' . htmlspecialchars($zainteresowanie['nazwa']) . '</span>';
                        echo '<span class="checkmark"></span>';
                        echo '</label>';
                    }
                    echo '</div>';
                } catch (PDOException $e) {
                    // w razie błędu połączenia z bazą pokazuję komunikat
                    echo '<p style="color: red;">Błąd pobierania zainteresowań: ' . $e->getMessage() . '</p>';
                }
                ?>
            </div>

            <!-- przycisk submit wysyłający cały formularz -->
            <button type="submit" class="btn">Zarejestruj się</button>
        </form>
    </div>
    
    <script>
    // obsługa zaznaczania kart zainteresowań
    document.querySelectorAll('.interest-card input[type="checkbox"]').forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            if (this.checked) {
                this.closest('.interest-card').classList.add('selected');
            } else {
                this.closest('.interest-card').classList.remove('selected');
            }
        });
    });
    </script>
</body>
</html>
