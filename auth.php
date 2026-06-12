<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$configFile = __DIR__ . '/JSON/config_data.json';
$configData = file_exists($configFile) ? json_decode(file_get_contents($configFile), true) : [];
$requireAuth = isset($configData['settings']['REQUIRE_AUTH']) ? $configData['settings']['REQUIRE_AUTH'] : (defined('REQUIRE_AUTH') ? REQUIRE_AUTH : false);

if ($requireAuth) {
    global $APP_USERS;

    // Converteer de nieuwe 1-lijn syntax ('name, pass, level') naar een makkelijker formaat
    $parsed_users = [];
    $configFile = __DIR__ . '/JSON/config_data.json';
    if (file_exists($configFile)) {
        $configData = json_decode(file_get_contents($configFile), true);
        if (isset($configData['users']) && is_array($configData['users'])) {
            $parsed_users = $configData['users'];
        }
    }

    // Fallback naar config.php $APP_USERS als de nieuwe manier nog niet volledig gevuld is
    if (empty($parsed_users) && isset($APP_USERS) && is_array($APP_USERS)) {
        foreach ($APP_USERS as $key => $val) {
            if (is_int($key) && is_string($val) && strpos($val, ',') !== false) {
                // Nieuwe syntax: 'name, pass, level'
                $parts = array_map('trim', explode(',', $val));
                if (count($parts) >= 2) {
                    $u = $parts[0];
                    $p = $parts[1];
                    $lvl = isset($parts[2]) ? (int)$parts[2] : 10;
                    $parsed_users[$u] = ['password' => $p, 'level' => $lvl];
                }
            } else {
                // Oude syntax fallback (key = name, val = pass)
                $parsed_users[$key] = ['password' => $val, 'level' => 10];
            }
        }
    }

    // Uitloggen
    if (isset($_GET['logout'])) {
        unset($_SESSION['authenticated']);
        unset($_SESSION['authenticated_user']);
        setcookie('auth_token', '', time() - 3600, '/');
        header("Location: index.php");
        exit();
    }

    // Inloggen via formulier
    if (isset($_POST['app_username']) && isset($_POST['app_password'])) {
        $username = $_POST['app_username'];
        $password = $_POST['app_password'];

        if (isset($parsed_users[$username]) && $parsed_users[$username]['password'] === $password) {
            $_SESSION['authenticated'] = true;
            $_SESSION['authenticated_user'] = $username;
            $_SESSION['role_level'] = $parsed_users[$username]['level'];

            // Zet een "remember me" cookie voor 30 dagen
            $token = base64_encode($username . ':' . hash_hmac('sha256', $username, $password));
            setcookie('auth_token', $token, time() + (30 * 24 * 60 * 60), '/');

            // Redirect to the same page without POST data
            header("Location: " . $_SERVER['REQUEST_URI']);
            exit();
        } else {
            $auth_error = "Ongeldige gebruikersnaam of wachtwoord.";
        }
    }

    // Controleer cookie als sessie niet actief is
    if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
        if (isset($_COOKIE['auth_token'])) {
            $decoded = base64_decode($_COOKIE['auth_token']);
            if (strpos($decoded, ':') !== false) {
                list($cookieUser, $cookieHash) = explode(':', $decoded, 2);
                if (isset($parsed_users[$cookieUser])) {
                    $expectedHash = hash_hmac('sha256', $cookieUser, $parsed_users[$cookieUser]['password']);
                    if (hash_equals($expectedHash, $cookieHash)) {
                        $_SESSION['authenticated'] = true;
                        $_SESSION['authenticated_user'] = $cookieUser;
                        $_SESSION['role_level'] = $parsed_users[$cookieUser]['level'];
                    }
                }
            }
        }
    }

    // Controleer definitief of de gebruiker is ingelogd
    if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
        $isJs = (basename($_SERVER['PHP_SELF']) === 'ha_core_js.php');
        if ($isJs) {
            header("Content-type: application/javascript; charset=utf-8");
            exit("/* Authentication required */ console.error('Authentication required');");
        }
        ?>
        <!DOCTYPE html>
        <html lang="nl">
        <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Toegang Vereist</title>
        <link href="https://fonts.googleapis.com/css2?family=Share+Tech+Mono&family=Barlow+Condensed:wght@300;400;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="CSS/common.css">
        <link rel="stylesheet" href="CSS/auth.css">
        <script>
          (function() {
            var theme = localStorage.getItem('theme');
            if (theme) {
              document.documentElement.setAttribute('data-theme', theme);
            }
          })();
        </script>
        </head>
        <body>
          <div class="login-card">
            <h1>🔒 BEVEILIGDE TOEGANG</h1>
            <?php if (isset($auth_error)): ?>
              <div class="error-msg"><?php echo htmlspecialchars($auth_error); ?></div>
            <?php endif; ?>
            <form method="POST">
              <input type="text" name="app_username" class="login-input" placeholder="Gebruikersnaam" required autofocus>
              <input type="password" name="app_password" class="login-input" placeholder="Wachtwoord" required>
              <button type="submit" class="login-btn">INLOGGEN</button>
            </form>
          </div>
        </body>
        </html>
        <?php
        exit();
    }
}
