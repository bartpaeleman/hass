<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$configFile = __DIR__ . '/JSON/config_data.json';
$configData = file_exists($configFile) ? json_decode(file_get_contents($configFile), true) : [];

// Auto-merge missing pages from example config
$exampleConfigFile = __DIR__ . '/JSON/config_data.example.json';
if (file_exists($exampleConfigFile)) {
    $exampleData = json_decode(file_get_contents($exampleConfigFile), true);
    if (isset($exampleData['pages']) && is_array($exampleData['pages'])) {
        if (!isset($configData['pages'])) {
            $configData['pages'] = [];
        }
        foreach ($exampleData['pages'] as $pageKey => $pageInfo) {
            if (!isset($configData['pages'][$pageKey])) {
                $configData['pages'][$pageKey] = $pageInfo;
            }
        }
    }
}

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

    // Check if the current page allows unauthenticated access (-1 role)
    $currentPage = basename($_SERVER['PHP_SELF']);
    $pageAllowsUnauth = false;
    if (isset($configData['pages'][$currentPage]['roles']) && in_array(-1, $configData['pages'][$currentPage]['roles'])) {
        $pageAllowsUnauth = true;
    }

    // Controleer definitief of de gebruiker is ingelogd
    if ((!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) && !$pageAllowsUnauth) {
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

            <?php
              $showWk2026Link = !isset($configData['settings']['SHOW_WK2026_LINK']) || !empty($configData['settings']['SHOW_WK2026_LINK']);
              if ($showWk2026Link):
            ?>
            <div style="margin-top: 24px;">
                <div class="section-label" style="color: var(--text-muted); font-size: 14px; margin-bottom: 8px;">
                    <svg width="12" height="12" viewBox="0 0 12 12" fill="none" style="margin-right: 4px;"><path d="M2 3h8M2 6h8M2 9h8" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/></svg>
                    Publieke Toegang
                </div>
                <a href="wk2026.php" style="display: flex; align-items: center; justify-content: space-between; padding: 16px; background: rgba(255,255,255,0.05); border: 1px solid var(--border); border-radius: 6px; text-decoration: none; color: var(--text-bright); transition: border-color 0.3s;" onmouseover="this.style.borderColor='var(--accent)'" onmouseout="this.style.borderColor='var(--border)'">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <span style="font-size: 24px;">⚽</span>
                        <div style="text-align: left;">
                            <div style="font-family: 'Share Tech Mono', monospace; font-size: 18px; font-weight: bold; color: var(--accent);">WK VOETBAL 2026</div>
                            <div style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">Bekijk standen, poules en het speelschema</div>
                        </div>
                    </div>
                    <div style="color: var(--accent);">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
                    </div>
                </a>
            </div>
            <?php endif; ?>
          </div>
        </body>
        </html>
        <?php
        exit();
    }
}
