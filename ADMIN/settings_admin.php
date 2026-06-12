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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_globals') {
        $configData['settings']['HA_URL'] = $_POST['ha_url'] ?? '';
        $configData['settings']['HA_TOKEN'] = $_POST['ha_token'] ?? '';
        $configData['settings']['REQUIRE_AUTH'] = isset($_POST['require_auth']);

        if (file_put_contents($configFile, json_encode($configData, JSON_PRETTY_PRINT)) !== false) {
            $message = "Globale instellingen opgeslagen.";
        } else {
            $error = "Fout bij opslaan van instellingen (rechten probleem?).";
        }
    }

    if ($action === 'add_user' || $action === 'edit_user') {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $level = (int)($_POST['level'] ?? 10);
        $original_username = trim($_POST['original_username'] ?? '');

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

            if (!$error && file_put_contents($configFile, json_encode($configData, JSON_PRETTY_PRINT)) !== false) {
                $message = "Gebruiker opgeslagen.";
            } else if (!$error) {
                $error = "Fout bij opslaan van instellingen (rechten probleem?).";
            }
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
            if (file_put_contents($configFile, json_encode($configData, JSON_PRETTY_PRINT)) !== false) {
                $message = "Gebruiker gewist.";
            } else {
                $error = "Fout bij opslaan van instellingen.";
            }
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
        if (file_put_contents($configFile, json_encode($configData, JSON_PRETTY_PRINT)) !== false) {
            $message = "Pagina instellingen opgeslagen.";
        } else {
            $error = "Fout bij opslaan van instellingen.";
        }
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
        .settings-section {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
        }
        .settings-section h2 {
            margin-top: 0;
            color: var(--accent);
            font-size: 24px;
            margin-bottom: 20px;
            border-bottom: 1px solid var(--border);
            padding-bottom: 10px;
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

    <div class="settings-section">
        <h2>Globale Instellingen</h2>
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
            <button type="submit" class="btn btn-save">Opslaan</button>
        </form>
    </div>

    <div class="settings-section">
        <h2>Account Management</h2>
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
                            <input type="number" form="edit_form_<?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?>" name="level" value="<?php echo htmlspecialchars($userData['level'], ENT_QUOTES, 'UTF-8'); ?>" style="width:80px; padding:8px; background:rgba(0,0,0,0.5); border:1px solid var(--border); color:var(--text-bright); border-radius:4px;" <?php echo (!empty($userData['is_default']) || $userData['level'] == 99) ? 'readonly style="opacity: 0.7;"' : ''; ?>>
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
                        <td><input type="number" form="add_user_form" name="level" value="50" style="width:80px; padding:8px; background:rgba(0,0,0,0.5); border:1px solid var(--border); color:var(--text-bright); border-radius:4px;" required></td>
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

    <div class="settings-section">
        <h2>Role Based Access (RBAC) & Pagina Beheer</h2>
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
                    $available_roles = [99 => 'Admin (99)', 50 => 'User (50)', 10 => 'Viewer (10)', 0 => 'Restricted (0)'];
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

</div>

</body>
</html>