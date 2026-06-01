<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (defined('REQUIRE_AUTH') && REQUIRE_AUTH) {
    global $APP_USERS;

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

        if (isset($APP_USERS[$username]) && $APP_USERS[$username] === $password) {
            $_SESSION['authenticated'] = true;
            $_SESSION['authenticated_user'] = $username;

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
                if (isset($APP_USERS[$cookieUser])) {
                    $expectedHash = hash_hmac('sha256', $cookieUser, $APP_USERS[$cookieUser]);
                    if (hash_equals($expectedHash, $cookieHash)) {
                        $_SESSION['authenticated'] = true;
                        $_SESSION['authenticated_user'] = $cookieUser;
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
        <style>
          body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background: var(--bg);
            color: var(--text);
          }
          .login-card {
            background: var(--surface);
            padding: 40px;
            border-radius: 12px;
            border: 1px solid var(--border);
            width: 100%;
            max-width: 400px;
            text-align: center;
            box-shadow: 0 8px 24px rgba(0,0,0,0.5);
          }
          .login-card h1 {
            font-family: 'Barlow Condensed', sans-serif;
            color: var(--text-bright);
            margin-bottom: 24px;
            font-size: 28px;
            letter-spacing: 1px;
          }
          .login-input {
            width: 100%;
            padding: 12px 16px;
            margin-bottom: 20px;
            border: 1px solid var(--border);
            border-radius: 6px;
            background: var(--bg);
            color: var(--text-bright);
            font-family: 'Share Tech Mono', monospace;
            font-size: 16px;
            box-sizing: border-box;
          }
          .login-input:focus {
            outline: none;
            border-color: var(--accent);
          }
          .login-btn {
            width: 100%;
            padding: 14px;
            background: var(--accent);
            color: #fff;
            border: none;
            border-radius: 6px;
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 20px;
            font-weight: bold;
            cursor: pointer;
            transition: opacity 0.3s, transform 0.1s;
          }
          .login-btn:hover {
            opacity: 0.9;
          }
          .login-btn:active {
            transform: scale(0.98);
          }
          .error-msg {
            color: var(--alert);
            margin-bottom: 20px;
            font-family: 'Share Tech Mono', monospace;
            font-size: 14px;
            background: rgba(255, 61, 61, 0.1);
            padding: 10px;
            border-radius: 4px;
            border: 1px solid rgba(255, 61, 61, 0.3);
          }
        </style>
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
