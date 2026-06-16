<?php
require_once __DIR__ . '/../config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['role_level']) || $_SESSION['role_level'] < 99) {
    header("HTTP/1.1 403 Forbidden");
    exit("Toegang geweigerd. Onvoldoende rechten.");
}

$message = '';
$error = '';

$configFile = __DIR__ . '/../JSON/config_data.json';
$exampleFile = __DIR__ . '/../JSON/config_data.example.json';

if (!file_exists($configFile) && file_exists($exampleFile)) {
    // Attempt to copy default settings
    @copy($exampleFile, $configFile);
}

$configData = file_exists($configFile) ? json_decode(file_get_contents($configFile), true) : [
    'users' => [
        'admin' => ['password' => 'admin', 'level' => 99, 'is_default' => true]
    ],
    'pages' => [
        'index.php' => ['name' => 'START', 'emoji' => '🏠', 'roles' => [99, 50, 10, 0]],
        'informatie.php' => ['name' => 'INFORMATIE', 'emoji' => 'ℹ️', 'roles' => [99, 50, 10, 0]],
        'automatisering.php' => ['name' => 'AUTOMATISERING', 'emoji' => '🤖', 'roles' => [99, 50, 10, 0]],
        'monitoring.php' => ['name' => 'ALARM', 'emoji' => '🚨', 'roles' => [99, 50, 10, 0]],
        'energy.php' => ['name' => 'ENERGIE', 'emoji' => '⚡', 'roles' => [99, 50, 10, 0]],
        'verwarming.php' => ['name' => 'VERWARMING', 'emoji' => '🔥', 'roles' => [99, 50, 10, 0]],
        'airco.php' => ['name' => 'AIRCO', 'emoji' => '❄️', 'roles' => [99, 50, 10, 0]],
        'verlichting.php' => ['name' => 'VERLICHTING', 'emoji' => '💡', 'roles' => [99, 50, 10, 0]],
        'kalender.php' => ['name' => 'KALENDER', 'emoji' => '🗓️', 'roles' => [99, 50, 10, 0]],
        'wk2026.php' => ['name' => 'WK VOETBAL', 'emoji' => '⚽', 'roles' => [99, 50, 10, 0]]
    ],
    'settings' => [
        'REQUIRE_AUTH' => true,
        'HA_URL' => '',
        'HA_TOKEN' => ''
    ]
];

// Auto-merge missing pages from example config
if (file_exists($exampleFile)) {
    $exampleData = json_decode(file_get_contents($exampleFile), true);
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

// Helper to save and handle errors
function saveConfig($file, $data) {
    global $message, $error;
    if (@file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT)) !== false) {
        $message = "Instellingen succesvol opgeslagen.";
        return true;
    } else {
        $error = "Fout bij opslaan van instellingen. Zorg ervoor dat het bestand schrijfbaar is (bijv. 'chmod 666 JSON/config_data.json' of de map 'chmod 777 JSON').";
        return false;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_globals') {
        $configData['settings']['HA_URL'] = $_POST['ha_url'] ?? '';
        $configData['settings']['HA_TOKEN'] = $_POST['ha_token'] ?? '';
        $configData['settings']['REQUIRE_AUTH'] = isset($_POST['require_auth']);
        $configData['settings']['SHOW_WK2026_LINK'] = isset($_POST['show_wk2026_link']);

        saveConfig($configFile, $configData);
    }

    if ($action === 'update_weather') {
        $configData['settings']['WEATHER_LATITUDE'] = $_POST['weather_lat'] ?? '';
        $configData['settings']['WEATHER_LONGITUDE'] = $_POST['weather_lon'] ?? '';
        $configData['settings']['WEATHER_ADDRESS'] = $_POST['weather_address'] ?? '';
        $configData['settings']['WEATHER_DAYS'] = (int)($_POST['weather_days'] ?? 7);
        $configData['settings']['WEATHER_SHOW_MIN_TEMP'] = isset($_POST['weather_show_min']);
        $configData['settings']['WEATHER_SHOW_MAX_TEMP'] = isset($_POST['weather_show_max']);
        $configData['settings']['WEATHER_SHOW_PRECIPITATION'] = isset($_POST['weather_show_precip']);
        $configData['settings']['WEATHER_SHOW_WIND'] = isset($_POST['weather_show_wind']);

        saveConfig($configFile, $configData);
    }

    if ($action === 'add_user' || $action === 'edit_user') {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $level = (int)($_POST['level'] ?? 10);
        $original_username = trim($_POST['original_username'] ?? '');

        // Disabled form elements are not submitted. If editing the default user, force level 99.
        if ($action === 'edit_user' && $original_username && !empty($configData['users'][$original_username]['is_default'])) {
            $level = 99;
        }

        if ($username && $password) {
            if ($action === 'edit_user' && $original_username && $original_username !== $username) {
                $is_default = !empty($configData['users'][$original_username]['is_default']);
                unset($configData['users'][$original_username]);
                $configData['users'][$username] = ['password' => $password, 'level' => $level];
                if ($is_default) {
                    $configData['users'][$username]['is_default'] = true;
                }
            } else {
                $is_default = !empty($configData['users'][$username]['is_default']);
                $configData['users'][$username] = ['password' => $password, 'level' => $level];
                if ($is_default) {
                    $configData['users'][$username]['is_default'] = true;
                }
            }

            if (!$error) { saveConfig($configFile, $configData); }
        } else {
            $error = "Gebruikersnaam en wachtwoord zijn verplicht.";
        }
    }

    if ($action === 'delete_user') {
        $username = $_POST['username'] ?? '';
        if (!empty($configData['users'][$username]['is_default'])) {
            $error = "Het standaard admin account kan niet worden gewist.";
        } else if (isset($configData['users'][$username])) {
            unset($configData['users'][$username]);
            saveConfig($configFile, $configData);
        }
    }

    if ($action === 'update_pages') {
        $pagesData = $_POST['pages'] ?? [];
        foreach ($pagesData as $filename => $data) {
            if (isset($configData['pages'][$filename])) {
                $configData['pages'][$filename]['name'] = $data['name'];
                $configData['pages'][$filename]['emoji'] = $data['emoji'];

                if ($filename === 'index.php') {
                    // index.php is ALL ON, do not change roles
                } else {
                    $roles = [];
                    if (isset($data['roles']) && is_array($data['roles'])) {
                        foreach ($data['roles'] as $role) {
                            $roles[] = (int)$role;
                        }
                    }
                    $configData['pages'][$filename]['roles'] = $roles;
                }
            }
        }
        saveConfig($configFile, $configData);
    }
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instellingen Beheer</title>
    <link href="https://fonts.googleapis.com/css2?family=Share+Tech+Mono&family=Barlow+Condensed:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../CSS/common.css">
    <link rel="stylesheet" href="../CSS/events_admin.css">
    <style>
        details.settings-section {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border);
            border-radius: 8px;
            margin-bottom: 30px;
        }
        details.settings-section summary {
            margin-top: 0;
            color: var(--accent);
            font-size: 24px;
            padding: 20px;
            cursor: pointer;
            list-style: none;
            user-select: none;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        details.settings-section summary::-webkit-details-marker {
            display: none;
        }
        details.settings-section summary::after {
            content: '▼';
            font-size: 18px;
            transition: transform 0.3s ease;
        }
        details.settings-section[open] summary::after {
            transform: rotate(180deg);
        }
        details.settings-section[open] summary {
            border-bottom: 1px solid var(--border);
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        details.settings-section .settings-content {
            padding: 0 20px 20px 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: var(--text-bright);
        }
        .form-group input[type="text"],
        .form-group input[type="password"] {
            width: 100%;
            padding: 10px;
            background: rgba(0, 0, 0, 0.5);
            border: 1px solid var(--border);
            color: var(--text-bright);
            border-radius: 4px;
            font-family: inherit;
        }
        .form-group input[type="checkbox"] {
            margin-right: 10px;
        }
        .btn-save {
            background: var(--ok);
            color: #000;
        }
        .btn-delete {
            background: var(--alert);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid var(--border);
        }
        th {
            color: var(--accent);
            text-transform: uppercase;
        }
        .user-row:hover, .page-row:hover {
            background: rgba(255, 255, 255, 0.02);
        }
    </style>
</head>
<body>

<div class="admin-container">
    <h1>⚙️ Instellingen Beheer</h1>

    <?php if ($message): ?>
        <div class="message"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="message" style="background: rgba(255, 61, 61, 0.1); border-color: var(--alert); color: var(--alert);">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <div style="margin-bottom: 20px;">
        <a href="../index.php" class="btn" style="text-decoration: none; background: rgba(255,255,255,0.1); color: var(--text-bright); padding: 10px 20px; font-size: 16px; display: inline-block;">⬅️ Dashboard</a>
    </div>

    <details class="settings-section">
        <summary>Globale Instellingen</summary>
        <div class="settings-content">
        <form method="POST">
            <input type="hidden" name="action" value="update_globals">
            <div class="form-group">
                <label>Home Assistant URL</label>
                <input type="text" name="ha_url" value="<?php echo htmlspecialchars($configData['settings']['HA_URL'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="form-group">
                <label>Home Assistant Token</label>
                <input type="password" name="ha_token" value="<?php echo htmlspecialchars($configData['settings']['HA_TOKEN'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="form-group" style="display: flex; align-items: center;">
                <input type="checkbox" id="require_auth" name="require_auth" <?php echo (!empty($configData['settings']['REQUIRE_AUTH'])) ? 'checked' : ''; ?>>
                <label for="require_auth" style="margin-bottom: 0;">Require Auth (Inloggen verplicht)</label>
            </div>
            <div class="form-group" style="display: flex; align-items: center;">
                <input type="checkbox" id="show_wk2026_link" name="show_wk2026_link" <?php echo (!isset($configData['settings']['SHOW_WK2026_LINK']) || !empty($configData['settings']['SHOW_WK2026_LINK'])) ? 'checked' : ''; ?>>
                <label for="show_wk2026_link" style="margin-bottom: 0;">WK 2026 Link op Startscherm tonen</label>
            </div>
            <button type="submit" class="btn btn-save">Opslaan</button>
        </form>
        </div>
    </details>

    <details class="settings-section">
        <summary>Weersvoorspelling Instellingen</summary>
        <div class="settings-content">
        <form method="POST">
            <input type="hidden" name="action" value="update_weather">
            <div class="form-group" style="background: rgba(255,255,255,0.05); padding: 12px; border-radius: 6px; margin-bottom: 15px;">
                <label>Locatie & Automatisch Bepalen</label>
                <div style="display: flex; gap: 10px; margin-bottom: 10px; flex-wrap: wrap;">
                    <input type="text" id="weather_address" name="weather_address" value="<?php echo htmlspecialchars($configData['settings']['WEATHER_ADDRESS'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Adres of referentie (wordt getoond op startscherm)" style="flex: 1; min-width: 200px;">
                    <button type="button" class="btn" style="background: var(--surface); border: 1px solid var(--border);" onclick="geocodeAddress()">🔍 Zoek Adres</button>
                    <button type="button" class="btn" style="background: var(--surface); border: 1px solid var(--border);" onclick="getCurrentLocation()">📍 Huidige Locatie</button>
                </div>
                <small style="color: var(--text); opacity: 0.7;">Zoeken gebruikt de gratis OpenStreetMap service. Huidige locatie vereist browser toestemming.</small>
            </div>

            <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                <div class="form-group" style="flex: 1;">
                    <label>Latitude (Breedtegraad)</label>
                    <input type="text" id="weather_lat" name="weather_lat" value="<?php echo htmlspecialchars($configData['settings']['WEATHER_LATITUDE'] ?? '51.32', ENT_QUOTES, 'UTF-8'); ?>" placeholder="bv. 51.32">
                </div>
                <div class="form-group" style="flex: 1;">
                    <label>Longitude (Lengtegraad)</label>
                    <input type="text" id="weather_lon" name="weather_lon" value="<?php echo htmlspecialchars($configData['settings']['WEATHER_LONGITUDE'] ?? '4.95', ENT_QUOTES, 'UTF-8'); ?>" placeholder="bv. 4.95">
                </div>
            </div>

            <div class="form-group">
                <label>Aantal Dagen (Max 16)</label>
                <input type="number" name="weather_days" value="<?php echo htmlspecialchars($configData['settings']['WEATHER_DAYS'] ?? 7, ENT_QUOTES, 'UTF-8'); ?>" min="1" max="16">
            </div>
            <div class="form-group" style="display: flex; align-items: center; gap: 15px; margin-top: 10px; margin-bottom: 5px;">
                <label style="margin-bottom: 0; display: flex; align-items: center; gap: 5px;">
                    <input type="checkbox" name="weather_show_min" <?php echo (!isset($configData['settings']['WEATHER_SHOW_MIN_TEMP']) || $configData['settings']['WEATHER_SHOW_MIN_TEMP']) ? 'checked' : ''; ?>>
                    Min Temp tonen
                </label>
                <label style="margin-bottom: 0; display: flex; align-items: center; gap: 5px;">
                    <input type="checkbox" name="weather_show_max" <?php echo (!isset($configData['settings']['WEATHER_SHOW_MAX_TEMP']) || $configData['settings']['WEATHER_SHOW_MAX_TEMP']) ? 'checked' : ''; ?>>
                    Max Temp tonen
                </label>
            </div>
            <div class="form-group" style="display: flex; align-items: center; gap: 15px; margin-bottom: 15px;">
                <label style="margin-bottom: 0; display: flex; align-items: center; gap: 5px;">
                    <input type="checkbox" name="weather_show_precip" <?php echo (!empty($configData['settings']['WEATHER_SHOW_PRECIPITATION'])) ? 'checked' : ''; ?>>
                    Neerslag tonen
                </label>
                <label style="margin-bottom: 0; display: flex; align-items: center; gap: 5px;">
                    <input type="checkbox" name="weather_show_wind" <?php echo (!empty($configData['settings']['WEATHER_SHOW_WIND'])) ? 'checked' : ''; ?>>
                    Windkracht tonen
                </label>
            </div>
            <button type="submit" class="btn btn-save">Opslaan</button>
        </form>
        </div>
    </details>

    <details class="settings-section" open>
        <summary>Account Management</summary>
        <div class="settings-content">
        <table>
            <thead>
                <tr>
                    <th>Gebruikersnaam</th>
                    <th>Wachtwoord</th>
                    <th>Toegangslevel</th>
                    <th>Acties</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($configData['users'] as $username => $userData): ?>
                <tr class="user-row">
                        <td>
                            <input type="text" form="edit_form_<?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?>" name="username" value="<?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?>">
                        </td>
                        <td>
                            <input type="text" form="edit_form_<?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?>" name="password" value="<?php echo htmlspecialchars($userData['password'], ENT_QUOTES, 'UTF-8'); ?>">
                        </td>
                        <td>
                            <select form="edit_form_<?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?>" name="level" style="padding:8px; background:rgba(0,0,0,0.5); border:1px solid var(--border); color:var(--text-bright); border-radius:4px; font-family: inherit;" <?php echo (!empty($userData['is_default'])) ? 'disabled style="opacity: 0.7;"' : ''; ?>>
                                <?php
                                $roles = [99 => 'Admin', 50 => 'User', 10 => 'Viewer', 0 => 'Restricted', -1 => 'Unauthenticated'];
                                foreach ($roles as $lvl => $label) {
                                    $selected = ($userData['level'] == $lvl) ? 'selected' : '';
                                    echo "<option value=\"$lvl\" $selected>$label</option>";
                                }
                                ?>
                            </select>
                        </td>
                        <td>
                            <form id="edit_form_<?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?>" method="POST" style="display:inline;">
                                <input type="hidden" name="action" value="edit_user">
                                <input type="hidden" name="original_username" value="<?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?>">
                                <button type="submit" class="btn btn-save" style="padding: 6px 12px;">Opslaan</button>
                            </form>
                            <?php if (empty($userData['is_default'])): ?>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Gebruiker wissen?');">
                                <input type="hidden" name="action" value="delete_user">
                                <input type="hidden" name="username" value="<?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?>">
                                <button type="submit" class="btn btn-delete" style="padding: 6px 12px;">Wissen</button>
                            </form>
                            <?php endif; ?>
                        </td>
                </tr>
                <?php endforeach; ?>
                <tr class="user-row" style="background: rgba(0,255,0,0.05);">
                        <td><input type="text" form="add_user_form" name="username" placeholder="Nieuwe gebruiker" required></td>
                        <td><input type="text" form="add_user_form" name="password" placeholder="Wachtwoord" required></td>
                        <td><select form="add_user_form" name="level" style="padding:8px; background:rgba(0,0,0,0.5); border:1px solid var(--border); color:var(--text-bright); border-radius:4px; font-family: inherit;" required>
                                <option value="99">Admin</option>
                                <option value="50" selected>User</option>
                                <option value="10">Viewer</option>
                                <option value="0">Restricted</option>
                                <option value="-1">Unauthenticated</option>
                            </select></td>
                        <td>
                            <form id="add_user_form" method="POST" style="display:inline;">
                                <input type="hidden" name="action" value="add_user">
                                <button type="submit" class="btn btn-save" style="padding: 6px 12px;">Toevoegen</button>
                            </form>
                        </td>
                </tr>
            </tbody>
        </table>
        </div>
    </details>

    <details class="settings-section">
        <summary>Role Based Access (RBAC) & Pagina Beheer</summary>
        <div class="settings-content">
        <form method="POST">
            <input type="hidden" name="action" value="update_pages">
            <table>
                <thead>
                    <tr>
                        <th>Bestand</th>
                        <th>Emoji</th>
                        <th>Naam (Sidebar & Titel)</th>
                        <th>Toegang (Roles)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $available_roles = [99 => 'Admin', 50 => 'User', 10 => 'Viewer', 0 => 'Restricted', -1 => 'Unauthenticated'];
                    foreach ($configData['pages'] as $filename => $pageData):
                    ?>
                    <tr class="page-row">
                        <td><?php echo htmlspecialchars($filename, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td>
                            <input type="text" name="pages[<?php echo htmlspecialchars($filename, ENT_QUOTES, 'UTF-8'); ?>][emoji]" value="<?php echo htmlspecialchars($pageData['emoji'], ENT_QUOTES, 'UTF-8'); ?>" style="width:50px;">
                        </td>
                        <td>
                            <input type="text" name="pages[<?php echo htmlspecialchars($filename, ENT_QUOTES, 'UTF-8'); ?>][name]" value="<?php echo htmlspecialchars($pageData['name'], ENT_QUOTES, 'UTF-8'); ?>">
                        </td>
                        <td>
                            <?php if ($filename === 'index.php'): ?>
                                <span style="color:var(--ok);">ALL ON (Standaard)</span>
                            <?php else: ?>
                                <div style="display:flex; gap:10px; flex-wrap:wrap;">
                                    <?php foreach ($available_roles as $roleValue => $roleName): ?>
                                        <label style="display:inline-flex; align-items:center; margin-bottom:0;">
                                            <input type="checkbox" name="pages[<?php echo htmlspecialchars($filename, ENT_QUOTES, 'UTF-8'); ?>][roles][]" value="<?php echo $roleValue; ?>" <?php echo in_array($roleValue, $pageData['roles']) ? 'checked' : ''; ?>>
                                            <?php echo $roleName; ?>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <button type="submit" class="btn btn-save">Alle Pagina's Opslaan</button>
        </form>
        </div>
    </details>

</div>

<script>
async function reverseGeocode(lat, lon) {
    try {
        const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}`);
        const data = await response.json();
        if (data && data.display_name) {
            document.getElementById('weather_address').value = data.display_name;
        }
    } catch (e) {
        console.error("Reverse geocoding faalde", e);
    }
}

async function fallbackToIPLocation() {
    try {
        const response = await fetch('https://get.geojs.io/v1/ip/geo.json');
        const data = await response.json();
        if (data && data.latitude && data.longitude) {
            document.getElementById('weather_lat').value = parseFloat(data.latitude).toFixed(4);
            document.getElementById('weather_lon').value = parseFloat(data.longitude).toFixed(4);
            await reverseGeocode(data.latitude, data.longitude);
            alert('Locatie succesvol opgehaald via IP (fallback).');
        } else {
            alert('Fout bij ophalen locatie via IP-fallback.');
        }
    } catch (e) {
        alert('Fout bij ophalen locatie via IP-fallback.');
        console.error(e);
    }
}

function getCurrentLocation() {
    if (navigator.geolocation && window.isSecureContext) {
        navigator.geolocation.getCurrentPosition(async function(position) {
            const lat = position.coords.latitude.toFixed(4);
            const lon = position.coords.longitude.toFixed(4);
            document.getElementById('weather_lat').value = lat;
            document.getElementById('weather_lon').value = lon;
            await reverseGeocode(lat, lon);
            alert('Locatie succesvol opgehaald!');
        }, function(error) {
            console.warn('Geolocation API faalde (' + error.message + '). Val terug op IP.');
            fallbackToIPLocation();
        });
    } else {
        console.warn('Geolocation niet ondersteund of onveilige context. Val terug op IP.');
        fallbackToIPLocation();
    }
}

async function geocodeAddress() {
    const address = document.getElementById('weather_address').value;
    if (!address) {
        alert('Voer eerst een adres of stad in.');
        return;
    }

    try {
        const response = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(address)}`);
        const data = await response.json();

        if (data && data.length > 0) {
            document.getElementById('weather_lat').value = parseFloat(data[0].lat).toFixed(4);
            document.getElementById('weather_lon').value = parseFloat(data[0].lon).toFixed(4);
            document.getElementById('weather_address').value = data[0].display_name;
            alert(`Coördinaten gevonden voor: ${data[0].display_name}`);
        } else {
            alert('Geen resultaten gevonden voor dit adres.');
        }
    } catch (e) {
        alert('Er is een fout opgetreden bij het zoeken naar het adres.');
        console.error(e);
    }
}
</script>
</body>
</html>