<?php
// config.php - Centraal beheer van Home Assistant credentials
// Dit bestand wordt nooit naar de browser gestuurd.

// CLOUD BASED
//define('HA_URL', ''); 

// LOCAL URL
define('HA_URL', ''); 


define('HA_TOKEN', ''); 

// Authentication (Auth)
define('REQUIRE_AUTH', false); // Zet op true om paswoordbeveiliging in te schakelen
define('APP_PASSWORD', 'geheim123'); // Paswoord om in te loggen indien REQUIRE_AUTH true is

// Zorg ervoor dat het auth mechanisme geladen wordt (indien ingeschakeld)
require_once 'auth.php';
?>
