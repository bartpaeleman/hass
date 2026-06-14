# Role-Based Access Control (RBAC)

Dit document beschrijft het vernieuwde authenticatie- en rollensysteem voor de dashboards. Naast het controleren op een geldige gebruiker (via wachtwoord of remember-me cookie), wordt er nu ook een specifieke rol en bijbehorend toegangsniveau (role level) aan de sessie toegewezen.

## Authenticatie & Configuratie
Het authenticatiemechanisme en alle applicatie-instellingen worden primair beheerd via het bestand \`JSON/config_data.json\`. Dit bestand is toegevoegd aan \`.gitignore\` om te voorkomen dat inloggegevens per ongeluk op GitHub belanden. Via de web-interface (\`ADMIN/settings_admin.php\`, enkel toegankelijk voor level 99) kunnen deze instellingen veilig gewijzigd worden.

*(Voor achterwaartse compatibiliteit kijkt het systeem nog naar \`$APP_USERS\` in \`config.php\` indien de JSON nog geen gebruikers bevat).*

### Bestandssysteem & Rechten
Omdat het web-dashboard (via PHP) gegevens wegschrijft naar dit bestand, moet de server schrijfrechten hebben op de map en/of het bestand. Bij het opslaan van instellingen kan je de foutmelding krijgen *"Fout bij opslaan van instellingen"*. Los dit als volgt op in de CLI van je server:
```bash
# Zorg dat de JSON map beschrijfbaar is voor de webgebruiker (bijv. www-data)
sudo chown -R www-data:www-data JSON/
# Of wijzig de permissies (minder veilig, maar werkt lokaal):
chmod 777 JSON/
chmod 666 JSON/config_data.json
```

**Voorbeeldstructuur van \`JSON/config_data.json\`:**
```json
{
    "users": {
        "admin": {
            "password": "geheim_paswoord",
            "level": 99,
            "is_default": true
        },
        "gast": {
            "password": "welkom",
            "level": 10
        }
    },
    "pages": {
        "index.php": {
            "name": "START",
            "emoji": "🏠",
            "roles": [99, 50, 10, 0]
        },
        "energy.php": {
            "name": "ENERGIE",
            "emoji": "⚡",
            "roles": [99, 50]
        }
    },
    "settings": {
        "REQUIRE_AUTH": true,
        "HA_URL": "http://192.168.1.100:8123",
        "HA_TOKEN": "eyJhbG..."
    }
}
```

## Beschikbare Rollen en Niveaus

Om de vergelijking van rechten in de code eenvoudig en schaalbaar te houden, wordt aan elke rol een numeriek 'level' gekoppeld. Hoe hoger het getal, hoe meer rechten de sessie heeft.

| Rolnaam      | Level | Beschrijving |
| :---         | :---: | :--- |
| `admin`      | `99`  | Volledige toegang tot alle pagina's, acties en instellingen (zoals configurators en backend editors). |
| `user`       | `50`  | Een bewoner met de rechten om acties en apparaten in het huis aan te sturen en privacy-gevoelige statusgegevens in te zien. |
| `viewer`     | `10`  | Een algemene gebruiker (zoals een gast). Kan enkel informatie bekijken. Krijgt geen toegang tot acties, en kan bepaalde gevoelige informatie niet zien. |
| `restricted` | `0`   | De meest beperkte gebruiker. Kan slechts een gelimiteerd overzicht bekijken. |

*Het bijbehorende niveau wordt na het inloggen bewaard in `$_SESSION['role_level']`.*

## Afgeschermde Componenten en Functionaliteiten

Momenteel zijn de volgende specifieke restricties geïmplementeerd binnen de frontend en backend:

### 1. Acties naar Home Assistant en UI interacties
- **Bestand:** `ha_core_js.php` (Geldt voor elk dashboard dat dit script gebruikt)
- **Standaard Rechten Vereist:** `user` (Level >= 50)
- **Beschrijving:** In de centrale JavaScript helper is een globale klik interceptor gebouwd en de API-functie `haPost()` geblokkeerd voor accounts onder het vereiste niveau. Standaard is dit ingesteld op level 50, wat betekent dat `viewers` (10) en `restricted` (0) gebruikers geen scripts kunnen starten, apparaten kunnen bedienen (aan/uit) of andere instellingen kunnen overschrijven. Knoppen, filter elementen en clickables in het hoofd content gedeelte (buiten header en navigatie) worden eveneens geblokkeerd en de JavaScript API executie wordt gestopt.

**Afwijken per dashboard:**
Het vereiste minimum level kan per dashboard overschreven worden door in de `<head>` de variabele `PAGE_MIN_ACTION_LEVEL` in JavaScript te definiëren, *voordat* `ha_core_js.php` is ingeladen.
```html
<script>
  const PAGE_MIN_ACTION_LEVEL = 10;
</script>
```
Hierdoor is het bijvoorbeeld mogelijk om op het **Verlichting** dashboard `viewers` (10) wél knoppen te laten gebruiken, terwijl **Energie** en **Verwarming** standaard streng (50) blijven. Op het **Speciale Dagen** dashboard staat dit overschreven op `0`, zodat iedere ingelogde gebruiker door de maand-/categorie-filters kan navigeren.

### 2. Beheer Speciale Dagen ("Editor")
- **Bestanden:** `kalender.php` en `ADMIN/events_admin.php`
- **Rechten Vereist:** `user` (Level >= 50)
- **Beschrijving:**
  - De knop (`⚙️ BEHEER`) bovenaan het Speciale Dagen dashboard wordt uitsluitend getoond aan gebruikers met voldoende rechten.
  - De eigenlijke backend-editor interface (`ADMIN/events_admin.php`) blokkeert actief de toegang en retourneert een `403 Forbidden` foutmelding als een onbevoegde gebruiker toch probeert de URL rechtstreeks te openen.

### 3. Aanwezigheid (Monitoring Dashboard / Alarm)
- **Bestand:** `monitoring.php`
- **Rechten Vereist:** `user` (Level >= 50)
- **Beschrijving:** De lijst met personen en hun huidige locatie (zoals 'Thuis' of 'Weg'), weergegeven onder de sectie "Aanwezigheid", is afgeschermd. Zowel de HTML-structuur van de lijst als de onderliggende JavaScript logica die de data inlaadt, worden niet opgebouwd en uitgevoerd voor gebruikers met lagere rechten (`viewer` of `restricted`).

---
_In de toekomst kunnen er eenvoudig extra restricties in PHP/HTML worden ingebouwd door secties in te sluiten in een if-statement met `$_SESSION['role_level']` of door in JavaScript validaties toe te voegen met behulp van de globale constante `USER_ROLE_LEVEL` of `MIN_ACTION_LEVEL`._