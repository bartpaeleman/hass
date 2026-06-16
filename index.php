<?php require_once 'config.php';
$configFile = __DIR__ . '/JSON/config_data.json';
$configData = file_exists($configFile) ? json_decode(file_get_contents($configFile), true) : [];
$pageTitle = $configData['pages']['index.php']['name'] ?? 'START';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$currentRole = $_SESSION['role_level'] ?? 10;
$allowedRoles = $configData['pages']['index.php']['roles'] ?? [99, 50, 10, 0];
if ('index.php' !== 'index.php' && !in_array($currentRole, $allowedRoles) && $currentRole < 99) {
    header("HTTP/1.1 403 Forbidden");
    exit("Toegang geweigerd. Onvoldoende rechten voor deze pagina.");
}

$weatherConfig = [
    'lat' => $configData['settings']['WEATHER_LATITUDE'] ?? '51.32',
    'lon' => $configData['settings']['WEATHER_LONGITUDE'] ?? '4.95',
    'address' => $configData['settings']['WEATHER_ADDRESS'] ?? '',
    'days' => (int)($configData['settings']['WEATHER_DAYS'] ?? 7),
    'showMin' => !isset($configData['settings']['WEATHER_SHOW_MIN_TEMP']) || $configData['settings']['WEATHER_SHOW_MIN_TEMP'],
    'showMax' => !isset($configData['settings']['WEATHER_SHOW_MAX_TEMP']) || $configData['settings']['WEATHER_SHOW_MAX_TEMP'],
    'showPrecip' => !empty($configData['settings']['WEATHER_SHOW_PRECIPITATION']),
    'showWind' => !empty($configData['settings']['WEATHER_SHOW_WIND'])
];
?>
<!DOCTYPE html>
<html lang="nl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></title>
<link href="https://fonts.googleapis.com/css2?family=Share+Tech+Mono&family=Barlow+Condensed:wght@300;400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="CSS/common.css">
<link rel="stylesheet" href="CSS/energie.css">
<link rel="stylesheet" href="CSS/weersvoorspelling.css">
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

<header>
  <div class="logo">
    <div class="logo-icon"></div>
    <div>
      <h1><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></h1>
      <span>HOME</span>
    </div>
  </div>
  <div class="header-right">
    <div class="live-badge"><div class="live-dot"></div> LIVE</div>
    <div class="clock" id="lastRefresh" style="font-size:10px;margin-right:8px;"></div>
    <div class="clock" id="clock">--:--:--</div>
  </div>
</header>

<main>
  <div class="left">

    <!-- Weersverwachting -->
    <div class="weather-section">
      <div class="weather-location-header">
        📍 <?php echo htmlspecialchars(!empty($weatherConfig['address']) ? $weatherConfig['address'] : "Coördinaten: " . $weatherConfig['lat'] . ", " . $weatherConfig['lon'], ENT_QUOTES, 'UTF-8'); ?>
      </div>
      <div class="weather-row" id="weatherRow">
      </div>
    </div>

    <!-- Temperatuur -->
    <div class="energy-grid" style="margin-top: 24px;">
    <div class="col-12">
        <div class="section-label">
            <svg width="12" height="12" viewBox="0 0 12 12" fill="none"><path d="M6 1v6.5M6 10a2 2 0 100-4 2 2 0 000 4z" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/><path d="M8 3h1M8 5h1" stroke="currentColor" stroke-width="1" stroke-linecap="round"/></svg>
            Temperatuur
        </div>
        <div id="tempList"></div>
    </div>
    </div>


    <?php
      $showWk2026Link = !isset($configData['settings']['SHOW_WK2026_LINK']) || !empty($configData['settings']['SHOW_WK2026_LINK']);
      if ($showWk2026Link):
    ?>
    <div style="margin-top: 24px;">
        <div class="section-label">
            <svg width="12" height="12" viewBox="0 0 12 12" fill="none"><path d="M2 3h8M2 6h8M2 9h8" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/></svg>
            Evenementen
        </div>
        <a href="wk2026.php" style="display: flex; align-items: center; justify-content: space-between; padding: 16px; background: var(--surface); border: 1px solid var(--border); border-radius: 6px; text-decoration: none; color: var(--text-bright); transition: border-color 0.3s;" onmouseover="this.style.borderColor='var(--accent)'" onmouseout="this.style.borderColor='var(--border)'">
            <div style="display: flex; align-items: center; gap: 12px;">
                <span style="font-size: 24px;">⚽</span>
                <div>
                    <div style="font-family: 'Share Tech Mono', monospace; font-size: 18px; font-weight: bold; color: var(--accent);">WK VOETBAL 2026</div>
                    <div style="font-size: 14px; color: var(--text-muted); margin-top: 4px;">Bekijk standen, poules en het speelschema</div>
                </div>
            </div>
            <div style="color: var(--accent);">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
            </div>
        </a>
    </div>
    <?php endif; ?>

    </div>

  </div>

  <div class="right">
      <?php include 'sidebar.php'; ?>
  </div>
</main>

<script>
  const weatherConfig = <?php echo json_encode($weatherConfig); ?>;

  const REFRESH  = 3000;

  const ENTITIES = {
    alarm:        'input_boolean.alarm_actief',
    alarmZone:    'input_text.actieve_zone',
    tempLiving:   'sensor.woonkamer_temp',
    tempSlaap:    'sensor.slaapkamer_temp',
    tempBureau:   'sensor.bureau_temp',
    tempBuiten:   'sensor.thuis_outdoor_temperature'
  };





  function updateTemp(states) {
    const rooms = [
      { label: 'Leven',  id: ENTITIES.tempLiving, cold: 20, warm: 23 },
      { label: 'Slapen', id: ENTITIES.tempSlaap,  cold: null, warm: 22 },
      { label: 'Werken', id: ENTITIES.tempBureau, cold: 20, warm: 24 },
    ];
    const list = document.getElementById('tempList');
    list.innerHTML = '';

    rooms.forEach(r => {
      const val = parseFloat(stateVal(states[r.id]));
      if (isNaN(val)) return;

      let cls = 'prima';
      let statusText = 'Prima';

      if (r.cold && val < r.cold) {
        cls = 'koud';
        statusText = 'Te koud';
      } else if (r.warm && val >= r.warm) {
        cls = 'warm';
        statusText = 'Te warm';
      }

      const card = document.createElement('div');
      card.className = `temp-card ${cls}`;
      card.innerHTML = `
        <div class="temp-info">
          <span class="temp-label">${r.label}</span>
          <span class="temp-status">${statusText}</span>
        </div>
        <span class="temp-val-large">${val.toFixed(1)}°C</span>
      `;
      list.appendChild(card);
    });

    const buitenVal = parseFloat(stateVal(states[ENTITIES.tempBuiten]));
    if (!isNaN(buitenVal)) {
      const card = document.createElement('div');
      card.className = `temp-card prima`;
      card.innerHTML = `
        <div class="temp-info">
          <span class="temp-label">Buiten</span>
        </div>
        <span class="temp-val-large">${buitenVal.toFixed(1)}°C</span>
      `;
      list.appendChild(card);
    }
  }

  const weatherCodes = {
    0: { text: "Helder", icon: "https://cdn.jsdelivr.net/gh/basmilius/weather-icons/production/fill/all/clear-day.svg" },
    1: { text: "Meestal helder", icon: "https://cdn.jsdelivr.net/gh/basmilius/weather-icons/production/fill/all/partly-cloudy-day.svg" },
    2: { text: "Half bewolkt", icon: "https://cdn.jsdelivr.net/gh/basmilius/weather-icons/production/fill/all/cloudy.svg" },
    3: { text: "Bewolkt", icon: "https://cdn.jsdelivr.net/gh/basmilius/weather-icons/production/fill/all/overcast.svg" },
    45: { text: "Mist", icon: "https://cdn.jsdelivr.net/gh/basmilius/weather-icons/production/fill/all/fog.svg" },
    48: { text: "Rijmende mist", icon: "https://cdn.jsdelivr.net/gh/basmilius/weather-icons/production/fill/all/fog.svg" },
    51: { text: "Lichte motregen", icon: "https://cdn.jsdelivr.net/gh/basmilius/weather-icons/production/fill/all/drizzle.svg" },
    53: { text: "Matige motregen", icon: "https://cdn.jsdelivr.net/gh/basmilius/weather-icons/production/fill/all/drizzle.svg" },
    55: { text: "Dichte motregen", icon: "https://cdn.jsdelivr.net/gh/basmilius/weather-icons/production/fill/all/drizzle.svg" },
    56: { text: "Lichte ijskoude motregen", icon: "https://cdn.jsdelivr.net/gh/basmilius/weather-icons/production/fill/all/sleet.svg" },
    57: { text: "Dichte ijskoude motregen", icon: "https://cdn.jsdelivr.net/gh/basmilius/weather-icons/production/fill/all/sleet.svg" },
    61: { text: "Lichte regen", icon: "https://cdn.jsdelivr.net/gh/basmilius/weather-icons/production/fill/all/rain.svg" },
    63: { text: "Matige regen", icon: "https://cdn.jsdelivr.net/gh/basmilius/weather-icons/production/fill/all/rain.svg" },
    65: { text: "Zware regen", icon: "https://cdn.jsdelivr.net/gh/basmilius/weather-icons/production/fill/all/extreme-rain.svg" },
    66: { text: "Lichte ijzel", icon: "https://cdn.jsdelivr.net/gh/basmilius/weather-icons/production/fill/all/sleet.svg" },
    67: { text: "Zware ijzel", icon: "https://cdn.jsdelivr.net/gh/basmilius/weather-icons/production/fill/all/extreme-sleet.svg" },
    71: { text: "Lichte sneeuwval", icon: "https://cdn.jsdelivr.net/gh/basmilius/weather-icons/production/fill/all/snow.svg" },
    73: { text: "Matige sneeuwval", icon: "https://cdn.jsdelivr.net/gh/basmilius/weather-icons/production/fill/all/snow.svg" },
    75: { text: "Zware sneeuwval", icon: "https://cdn.jsdelivr.net/gh/basmilius/weather-icons/production/fill/all/extreme-snow.svg" },
    77: { text: "Sneeuwgries", icon: "https://cdn.jsdelivr.net/gh/basmilius/weather-icons/production/fill/all/snow.svg" },
    80: { text: "Lichte regenbuien", icon: "https://cdn.jsdelivr.net/gh/basmilius/weather-icons/production/fill/all/rain.svg" },
    81: { text: "Matige regenbuien", icon: "https://cdn.jsdelivr.net/gh/basmilius/weather-icons/production/fill/all/rain.svg" },
    82: { text: "Hevige regenbuien", icon: "https://cdn.jsdelivr.net/gh/basmilius/weather-icons/production/fill/all/extreme-rain.svg" },
    85: { text: "Lichte sneeuwbuien", icon: "https://cdn.jsdelivr.net/gh/basmilius/weather-icons/production/fill/all/snow.svg" },
    86: { text: "Zware sneeuwbuien", icon: "https://cdn.jsdelivr.net/gh/basmilius/weather-icons/production/fill/all/extreme-snow.svg" },
    95: { text: "Onweer", icon: "https://cdn.jsdelivr.net/gh/basmilius/weather-icons/production/fill/all/thunderstorms.svg" },
    96: { text: "Onweer met lichte hagel", icon: "https://cdn.jsdelivr.net/gh/basmilius/weather-icons/production/fill/all/thunderstorms-rain.svg" },
    99: { text: "Onweer met zware hagel", icon: "https://cdn.jsdelivr.net/gh/basmilius/weather-icons/production/fill/all/extreme-thunderstorms.svg" }
};

  async function loadWeather() {
      try {
          let dailyParams = ["weathercode"];
          if (weatherConfig.showMax) dailyParams.push("temperature_2m_max");
          if (weatherConfig.showMin) dailyParams.push("temperature_2m_min");
          if (weatherConfig.showPrecip) dailyParams.push("precipitation_probability_max");
          if (weatherConfig.showWind) dailyParams.push("wind_speed_10m_max");

          const dailyStr = dailyParams.join(",");
          const url = `https://api.open-meteo.com/v1/forecast?latitude=${weatherConfig.lat}&longitude=${weatherConfig.lon}&daily=${dailyStr}&forecast_days=${weatherConfig.days}&timezone=auto`;

          const response = await fetch(url);
          const data = await response.json();
          const container = document.getElementById("weatherRow");
          container.innerHTML = "";

          data.daily.time.forEach((date, index) => {
              const code = data.daily.weathercode[index];
              const weather = weatherCodes[code] || { text: "Onbekend", icon: "https://cdn.jsdelivr.net/gh/basmilius/weather-icons/production/fill/all/not-available.svg" };

              let html = `<div class="weather-card">`;

              const dayName = new Date(date).toLocaleDateString("nl-BE", { weekday: "short", day: "numeric", month: "short" });
              html += `<div class="weather-day">${dayName}</div>`;
              html += `<img class="weather-icon" src="${weather.icon}" alt="${weather.text}">`;

              if (weatherConfig.showMax && data.daily.temperature_2m_max) {
                  const max = Math.round(data.daily.temperature_2m_max[index]);
                  html += `<div class="weather-temp-max">${max}°C</div>`;
              }
              if (weatherConfig.showMin && data.daily.temperature_2m_min) {
                  const min = Math.round(data.daily.temperature_2m_min[index]);
                  html += `<div class="weather-temp-min">${min}°C</div>`;
              }

              html += `<div class="weather-description">${weather.text}</div>`;

              let extrasHtml = "";
              if (weatherConfig.showPrecip && data.daily.precipitation_probability_max) {
                  const precip = data.daily.precipitation_probability_max[index];
                  extrasHtml += `<div class="weather-extra"><span class="weather-extra-icon">💧</span> ${precip}%</div>`;
              }
              if (weatherConfig.showWind && data.daily.wind_speed_10m_max) {
                  const wind = Math.round(data.daily.wind_speed_10m_max[index]);
                  extrasHtml += `<div class="weather-extra"><span class="weather-extra-icon">💨</span> ${wind} km/u</div>`;
              }

              if (extrasHtml !== "") {
                  html += `<div class="weather-extras-container">${extrasHtml}</div>`;
              }

              html += `</div>`;
              container.innerHTML += html;
          });
      } catch (e) {
          console.error("Fout bij laden weersvoorspelling:", e);
      }
  }

  loadWeather();

  function tick() {
    document.getElementById('clock').textContent =
      new Date().toLocaleTimeString('nl-BE', { hour12: false });
  }
  tick();
  setInterval(tick, 1000);

  async function refresh() {
    const allIds = [
      ...Object.values(ENTITIES)
    ];

    if (allIds.length > 0) {
      const results = await haGetAll([...new Set(allIds)]);
      const stateMap = {};
      [...new Set(allIds)].forEach((id, i) => stateMap[id] = results[i]);

      updateTemp(stateMap);


    }

    document.getElementById('lastRefresh').textContent =
      'Bijgewerkt: ' + new Date().toLocaleTimeString('nl-BE');
  }

  refresh();
  setInterval(() => { if (!window.isRefreshPaused) refresh(); }, REFRESH);
</script>
</body>
</html>