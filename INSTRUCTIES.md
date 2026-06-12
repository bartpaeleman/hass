## Hoe koppel je de nieuwe Admin Instellingen aan je app?

Aangezien je in het verleden je eigen `config.php` op je webserver had staan en wij deze niet rechtstreeks konden overschrijven zonder mogelijke instellingen kwijt te spelen, maakt de admin interface nu gebruik van een apart configuratiebestand: `config_data.php`.

Om alles naadloos te laten werken, moet je jouw huidige `config.php` (lokaal) aanpassen zodat deze dynamisch de variabelen inlaadt die je via de nieuwe admin interface (`/ADMIN/settings_admin.php`) beheert.

Plaats simpelweg deze code in je lokale `config.php`:

```php
<?php
// Pad naar de dynamische settings
$configFile = __DIR__ . '/config_data.php';

// Zorg dat we een array inladen, indien het bestand nog niet is gegenereerd val je terug op een lege array
$configData = [];
if (file_exists($configFile)) {
    $configData = include $configFile;
    if (!is_array($configData)) {
        $configData = [];
    }
}

// === CONSTANTEN ===
// Definieer de app constanten. Als ze voorkomen in de nieuwe instellingen, gebruik die;
// gebruik anders de standaard hardcoded waarden die je nu hebt.
if (!defined('HA_URL')) {
    define('HA_URL', $configData['HA_URL'] ?? 'http://ha.local:8123');
}
if (!defined('HA_TOKEN')) {
    define('HA_TOKEN', $configData['HA_TOKEN'] ?? 'JOUW_HARDCODED_TOKEN');
}
if (!defined('REQUIRE_AUTH')) {
    define('REQUIRE_AUTH', $configData['REQUIRE_AUTH'] ?? true);
}
if (!defined('WK_VOETBAL_TITLE')) {
    define('WK_VOETBAL_TITLE', $configData['WK_VOETBAL_TITLE'] ?? 'WK VOETBAL');
}
if (!defined('WK_VOETBAL_VISIBLE')) {
    define('WK_VOETBAL_VISIBLE', isset($configData['WK_VOETBAL_VISIBLE']) ? $configData['WK_VOETBAL_VISIBLE'] : true);
}

// === GEBRUIKERS BEHEER ===
global $APP_USERS;
if (!empty($configData['APP_USERS'])) {
    $APP_USERS = $configData['APP_USERS'];
} else {
    // Jouw oude hardcoded fallback lijst:
    $APP_USERS = [
        'admin' => 'admin, paswoord123, 99',
        'viewer' => 'viewer, paswoord, 10'
    ];
}

require_once __DIR__ . '/auth.php';
```

Zodra je dit aanpast en inlogt, kan je naar `Instellingen` navigeren om alles daar veilig bij te werken.
