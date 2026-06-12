<?php
require_once __DIR__ . '/../config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Only level 99 can access this
if (!isset($_SESSION['role_level']) || $_SESSION['role_level'] < 99) {
    header("HTTP/1.1 403 Forbidden");
    exit("Toegang geweigerd. Enkel beheerders (level 99) hebben toegang tot deze pagina.");
}

$configFile = __DIR__ . '/../config_data.php';
$message = '';
$error = '';

$configData = [];
if (file_exists($configFile)) {
    $configData = include $configFile;
    if (!is_array($configData)) {
        $configData = [];
    }
} else {
    // Attempt migration from config.php if config_data doesn't exist
    global $APP_USERS;
    $configData = [
        "HA_URL" => defined('HA_URL') ? HA_URL : 'http://ha.local:8123',
        "HA_TOKEN" => defined('HA_TOKEN') ? HA_TOKEN : '',
        "REQUIRE_AUTH" => defined('REQUIRE_AUTH') ? REQUIRE_AUTH : true,
        "WK_VOETBAL_TITLE" => defined('WK_VOETBAL_TITLE') ? WK_VOETBAL_TITLE : 'WK VOETBAL',
        "WK_VOETBAL_VISIBLE" => defined('WK_VOETBAL_VISIBLE') ? WK_VOETBAL_VISIBLE : true,
        "APP_USERS" => isset($APP_USERS) && is_array($APP_USERS) ? $APP_USERS : []
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'save_settings') {
        $configData['HA_URL'] = $_POST['ha_url'] ?? '';
        $configData['HA_TOKEN'] = $_POST['ha_token'] ?? '';
        $configData['REQUIRE_AUTH'] = isset($_POST['require_auth']) ? true : false;
        $configData['WK_VOETBAL_TITLE'] = $_POST['wk_voetbal_title'] ?? 'WK VOETBAL';
        $configData['WK_VOETBAL_VISIBLE'] = isset($_POST['wk_voetbal_visible']) ? true : false;

        $phpCode = "<?php\nreturn " . var_export($configData, true) . ";\n";
        if (file_put_contents($configFile, $phpCode) !== false) {
            $message = "Instellingen succesvol opgeslagen in config_data.php. Vergeet niet je config.php aan te passen om dit bestand in te laden!";
        } else {
            $error = "Fout bij opslaan. Controleer of de webserver schrijfrechten heeft op config_data.php.";
        }
    } elseif ($action === 'add_user') {
        $newUsername = trim($_POST['new_username'] ?? '');
        $newPassword = $_POST['new_password'] ?? '';
        $newLevel = (int)($_POST['new_level'] ?? 50);

        if (empty($newUsername) || empty($newPassword)) {
            $error = "Naam en wachtwoord zijn verplicht.";
        } elseif (strpos($newUsername, ',') !== false || strpos($newPassword, ',') !== false) {
            $error = "Komma's zijn niet toegestaan in naam of wachtwoord.";
        } elseif (isset($configData['APP_USERS'][$newUsername])) {
            $error = "Deze gebruiker bestaat al.";
        } else {
            $configData['APP_USERS'][$newUsername] = "$newUsername, $newPassword, $newLevel";
            $phpCode = "<?php\nreturn " . var_export($configData, true) . ";\n";
            if (file_put_contents($configFile, $phpCode) !== false) {
                $message = "Gebruiker $newUsername toegevoegd.";
            } else {
                $error = "Fout bij opslaan.";
            }
        }
    } elseif ($action === 'edit_user') {
        $oldUsername = $_POST['old_username'] ?? '';
        $newUsername = trim($_POST['new_username'] ?? '');
        $newPassword = $_POST['new_password'] ?? '';
        $newLevel = (int)($_POST['new_level'] ?? 50);

        if (empty($newUsername) || empty($newPassword)) {
            $error = "Naam en wachtwoord zijn verplicht.";
        } elseif (strpos($newUsername, ',') !== false || strpos($newPassword, ',') !== false) {
            $error = "Komma's zijn niet toegestaan in naam of wachtwoord.";
        } elseif ($oldUsername === 'admin' && $newUsername !== 'admin') {
            $error = "De admin loginnaam kan niet gewijzigd worden.";
        } elseif ($oldUsername === 'admin' && $newLevel < 99) {
            $error = "Admin role_level moet altijd 99 zijn.";
        } elseif ($newUsername !== $oldUsername && isset($configData['APP_USERS'][$newUsername])) {
            $error = "De nieuwe gebruikersnaam bestaat al.";
        } elseif (isset($configData['APP_USERS'][$oldUsername])) {
            if ($newUsername !== $oldUsername) {
                unset($configData['APP_USERS'][$oldUsername]);
            }
            $configData['APP_USERS'][$newUsername] = "$newUsername, $newPassword, $newLevel";
            $phpCode = "<?php\nreturn " . var_export($configData, true) . ";\n";
            if (file_put_contents($configFile, $phpCode) !== false) {
                $message = "Gebruiker $newUsername bijgewerkt.";
            } else {
                $error = "Fout bij opslaan.";
            }
        } else {
            $error = "Te bewerken gebruiker niet gevonden.";
        }
    } elseif ($action === 'delete_user') {
        $username = $_POST['username'] ?? '';
        if ($username === 'admin') {
            $error = "De admin gebruiker kan niet verwijderd worden.";
        } elseif (isset($configData['APP_USERS'][$username])) {
            unset($configData['APP_USERS'][$username]);
            $phpCode = "<?php\nreturn " . var_export($configData, true) . ";\n";
            if (file_put_contents($configFile, $phpCode) !== false) {
                $message = "Gebruiker $username verwijderd.";
            } else {
                $error = "Fout bij opslaan.";
            }
        }
    }
}

// No need to reload config file here since we already have the modified $configData in memory
// and caching/opcache might prevent seeing the latest writes immediately.
$users = $configData['APP_USERS'] ?? [];
?>
<!DOCTYPE html>
<html lang="nl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Instellingen</title>
<link href="https://fonts.googleapis.com/css2?family=Share+Tech+Mono&family=Barlow+Condensed:wght@300;400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../CSS/common.css">
<style>
  body { padding: 20px; color: var(--text); background: var(--bg); font-family: 'Share Tech Mono', monospace; }
  .admin-container { max-width: 800px; margin: 0 auto; background: var(--surface); padding: 20px; border: 1px solid var(--border); border-radius: 8px; }
  h1, h2 { font-family: 'Barlow Condensed', sans-serif; color: var(--text-bright); margin-top: 0; }
  .form-group { margin-bottom: 15px; }
  .form-group label { display: block; margin-bottom: 5px; color: var(--text-muted); }
  .form-group input[type="text"], .form-group input[type="password"], .form-group input[type="number"] { width: 100%; padding: 8px; background: var(--bg); border: 1px solid var(--border); color: var(--text); border-radius: 4px; font-family: inherit; box-sizing: border-box; }
  .form-group input[type="checkbox"] { margin-right: 10px; }
  .btn { padding: 10px 15px; background: var(--accent); color: #fff; border: none; border-radius: 4px; cursor: pointer; font-family: 'Barlow Condensed', sans-serif; font-size: 16px; font-weight: bold; }
  .btn:hover { opacity: 0.9; }
  .btn-danger { background: var(--alert); }
  .msg-success { background: rgba(0,230,118,0.1); color: var(--ok); padding: 10px; border-left: 4px solid var(--ok); margin-bottom: 20px; }
  .msg-error { background: rgba(255,61,61,0.1); color: var(--alert); padding: 10px; border-left: 4px solid var(--alert); margin-bottom: 20px; }
  table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
  th, td { border: 1px solid var(--border); padding: 10px; text-align: left; }
  th { background: rgba(255,255,255,0.05); color: var(--text-bright); font-family: 'Barlow Condensed', sans-serif; font-size: 18px; }
  .flex-row { display: flex; gap: 10px; align-items: center; }
  .dashboard-link { display: inline-block; margin-bottom: 20px; text-decoration: none; color: var(--accent); }
  .dashboard-link:hover { text-decoration: underline; }
</style>
</head>
<body>

<div class="admin-container">
  <a href="../index.php" class="dashboard-link">⬅️ Terug naar Dashboard</a>
  <h1>⚙️ Systeem Instellingen</h1>

  <?php if (!empty($message)): ?>
    <div class="msg-success"><?php echo htmlspecialchars($message); ?></div>
  <?php endif; ?>
  <?php if (!empty($error)): ?>
    <div class="msg-error"><?php echo htmlspecialchars($error); ?></div>
  <?php endif; ?>

  <div style="background: rgba(255,179,0,0.1); border-left: 4px solid var(--warn); padding: 15px; margin-bottom: 30px;">
    <strong>Let op!</strong> Instellingen op deze pagina worden bewaard in <code>config_data.php</code>.
    Zorg ervoor dat in je hoofd-<code>config.php</code> bestand deze nieuwe waarden worden ingeladen.
  </div>

  <form method="POST" style="margin-bottom: 40px; padding-bottom: 20px; border-bottom: 1px solid var(--border);">
    <input type="hidden" name="action" value="save_settings">
    <h2>Algemene Parameters</h2>

    <div class="form-group">
      <label>HA URL</label>
      <input type="text" name="ha_url" value="<?php echo htmlspecialchars($configData['HA_URL'] ?? ''); ?>" required>
    </div>
    <div class="form-group">
      <label>HA TOKEN</label>
      <input type="password" name="ha_token" value="<?php echo htmlspecialchars($configData['HA_TOKEN'] ?? ''); ?>" required>
    </div>
    <div class="form-group flex-row">
      <input type="checkbox" name="require_auth" id="require_auth" <?php echo !empty($configData['REQUIRE_AUTH']) ? 'checked' : ''; ?>>
      <label for="require_auth" style="margin: 0;">Require Auth (Authenticatie vereist)</label>
    </div>

    <h2 style="margin-top: 30px;">Optionele Pagina's</h2>
    <div class="form-group">
      <label>WK/EK Voetbal Pagina Titel</label>
      <input type="text" name="wk_voetbal_title" value="<?php echo htmlspecialchars($configData['WK_VOETBAL_TITLE'] ?? 'WK VOETBAL'); ?>" required>
    </div>
    <div class="form-group flex-row">
      <input type="checkbox" name="wk_voetbal_visible" id="wk_voetbal_visible" <?php echo !empty($configData['WK_VOETBAL_VISIBLE']) ? 'checked' : ''; ?>>
      <label for="wk_voetbal_visible" style="margin: 0;">Toon WK Voetbal pagina voor alle gebruikers</label>
    </div>

    <button type="submit" class="btn" style="margin-top: 15px;">💾 OPSLAAN INSTELLINGEN</button>
  </form>

  <h2>👥 Gebruikersbeheer</h2>
  <table>
    <thead>
      <tr>
        <th>Username</th>
        <th>Wachtwoord</th>
        <th>Level</th>
        <th>Acties</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($users as $userKey => $userStr):
          $parts = array_map('trim', explode(',', $userStr));
          // Check if string contains 3 parts (name, pass, level) or 2 parts (pass, level)
          if (count($parts) >= 3) {
              $uName = $parts[0];
              $uPass = $parts[1];
              $uLvl = $parts[2];
          } else {
              $uName = $userKey;
              $uPass = $parts[0] ?? '';
              $uLvl = $parts[1] ?? 50;
          }
      ?>
      <tr>
        <form method="POST">
          <input type="hidden" name="old_username" value="<?php echo htmlspecialchars($uName); ?>">
          <td>
            <?php if ($uName === 'admin'): ?>
              <input type="text" name="new_username" value="admin" readonly style="width: 100px; background:var(--bg); color:var(--text-muted); border:1px solid var(--border); padding:4px;">
            <?php else: ?>
              <input type="text" name="new_username" value="<?php echo htmlspecialchars($uName); ?>" style="width: 100px; background:var(--bg); color:var(--text); border:1px solid var(--border); padding:4px;" required>
            <?php endif; ?>
          </td>
          <td>
            <input type="text" name="new_password" value="<?php echo htmlspecialchars($uPass); ?>" style="width: 100px; background:var(--bg); color:var(--text); border:1px solid var(--border); padding:4px;" required>
          </td>
          <td>
            <input type="number" name="new_level" value="<?php echo htmlspecialchars($uLvl); ?>" style="width: 60px; background:var(--bg); color:var(--text); border:1px solid var(--border); padding:4px;" required>
          </td>
          <td class="flex-row">
            <input type="hidden" name="action" value="edit_user">
            <button type="submit" class="btn" style="padding: 4px 8px; font-size: 14px;">BEWAREN</button>
        </form>

        <?php if ($uName !== 'admin'): ?>
        <form method="POST" onsubmit="return confirm('Weet je zeker dat je <?php echo htmlspecialchars($uName); ?> wilt verwijderen?');">
            <input type="hidden" name="action" value="delete_user">
            <input type="hidden" name="username" value="<?php echo htmlspecialchars($uName); ?>">
            <button type="submit" class="btn btn-danger" style="padding: 4px 8px; font-size: 14px;">WISSEN</button>
        </form>
        <?php endif; ?>
          </td>
      </tr>
      <?php endforeach; ?>

      <!-- Add new user row -->
      <tr style="background: rgba(255,255,255,0.02);">
        <form method="POST">
          <input type="hidden" name="action" value="add_user">
          <td><input type="text" name="new_username" placeholder="Nieuwe user" style="width: 100px; background:var(--bg); color:var(--text); border:1px solid var(--border); padding:4px;" required></td>
          <td><input type="text" name="new_password" placeholder="Wachtwoord" style="width: 100px; background:var(--bg); color:var(--text); border:1px solid var(--border); padding:4px;" required></td>
          <td><input type="number" name="new_level" value="50" style="width: 60px; background:var(--bg); color:var(--text); border:1px solid var(--border); padding:4px;" required></td>
          <td>
            <button type="submit" class="btn" style="padding: 4px 8px; font-size: 14px; background: var(--ok);">➕ TOEVOEGEN</button>
          </td>
        </form>
      </tr>
    </tbody>
  </table>

</div>

</body>
</html>
