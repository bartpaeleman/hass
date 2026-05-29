<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="nl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Start</title>
<link href="https://fonts.googleapis.com/css2?family=Share+Tech+Mono&family=Barlow+Condensed:wght@300;400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="CSS/common.css">
<link rel="stylesheet" href="CSS/energie.css">
<link rel="stylesheet" href="CSS/weersvoorspelling.css">
</head>
<body>

<header>
  <div class="logo">
    <div class="logo-icon"></div>
    <div>
      <h1>Start</h1>
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

    <div class="section-label" style="margin-top:20px;">
      <svg width="12" height="12" viewBox="0 0 12 12" fill="none"><path d="M3 6h6M6 3v6" stroke="currentColor" stroke-width="1.2" fill="none" stroke-linecap="round" stroke-linejoin="round"/></svg>
      Weersverwachting komende dagen (Turnhout)
    </div>
    <div class="weather-section">
      <div class="weather-row" id="weatherRow">
        </div>
    </div>

    <div class="section-label" style="margin-top:20px;">
      <svg width="12" height="12" viewBox="0 0 12 12" fill="none"><path d="M2 3h8M4 3V2h4v1M5 5v4M7 5v4M3 3l.5 7h5l.5-7" stroke="currentColor" stroke-width="1.2" fill="none" stroke-linecap="round" stroke-linejoin="round"/></svg>
      Afvalophaling
    </div>

    <div class="waste-row-2" id="wasteTodayTomorrowRow" style="margin-bottom:12px;">
      <div class="waste-card waste-card-flex" id="wasteTodayCard">
        <div style="text-align: left; min-width: 0;">
            <span class="waste-when">VANDAAG</span>
            <span class="waste-name" id="wasteTodayName" style="font-size:14px; margin-top:4px; display:block;">—</span>
        </div>
        <div id="wasteTodayVal" style="padding-right: 10px;"></div>
      </div>
      <div class="waste-card waste-card-flex" id="wasteTomorrowCard">
        <div style="text-align: left; min-width: 0;">
            <span class="waste-when">MORGEN</span>
            <span class="waste-name" id="wasteTomorrowName" style="font-size:14px; margin-top:4px; display:block;">—</span>
        </div>
        <div id="wasteTomorrowVal" style="padding-right: 10px;"></div>
      </div>
    </div>

    <div class="waste-row" id="wasteRow" style="margin-bottom:24px;">
      </div>

    <div class="energy-grid" style="margin-top: 24px;">
    <div class="energy-card col-4">
        <div class="energy-card-header">
            <div class="energy-card-info">
                <div class="energy-icon dark-grey">🤖</div>
                <div class="energy-title">Roomba</div>
            </div>
            <div class="energy-card-params">
                <div class="energy-value" id="roomba-status" style="font-size:16px; text-transform: uppercase;">—</div>
                <div style="font-size:13px; color:var(--text);">
                    Accu: <span id="roomba-accu" style="color:var(--text)">—</span>% | Bakje: <span id="roomba-bakje">—</span> | <span id="roomba-vol">—</span>
                </div>
            </div>
        </div>
    </div>

    <div class="energy-card col-4">
        <div class="energy-card-header">
            <div class="energy-card-info">
                <div class="energy-icon blue">🚪</div>
                <div class="energy-title">Voordeur</div>
            </div>
            <div class="energy-card-params">
                <div class="energy-value" id="deur-slot" style="font-size:16px; text-transform: uppercase;">—</div>
                <div style="font-size:13px; color:var(--text);">
                    Deur: <span id="deur-open" style="color:var(--text)">—</span> | Nuki: <span id="deur-bat">—</span>
                </div>
            </div>
        </div>
    </div>

    <div class="energy-card col-4">
        <div class="energy-card-header">
            <div class="energy-card-info">
                <div class="energy-icon grey">🌐</div>
                <div class="energy-title">Netwerk</div>
            </div>
            <div class="energy-card-params">
                <div class="energy-value" id="net-status" style="font-size:16px; text-transform: uppercase;">—</div>
                <div style="font-size:13px; color:var(--text);">
                    <span style="color:var(--accent)">↓</span> <span id="net-down">—</span> Mbps | <span style="color:var(--accent)">↑</span> <span id="net-up">—</span> Mbps | Ping: <span id="net-ping">—</span> ms
                </div>
            </div>
        </div>
    </div>
    </div>
    
    <div class="energy-grid" style="margin-top: 24px;">
    <div class="col-12">
        <div class="section-label">
            <svg width="12" height="12" viewBox="0 0 12 12" fill="none"><path d="M6 1v6.5M6 10a2 2 0 100-4 2 2 0 000 4z" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/><path d="M8 3h1M8 5h1" stroke="currentColor" stroke-width="1" stroke-linecap="round"/></svg>
            Temperatuur
        </div>
        <div id="tempList"></div>
    </div>
    </div>

  </div>

  <div class="right">
      <?php include 'sidebar.php'; ?>
  </div>
</main>

<script>
  const REFRESH  = 3000;

  const ENTITIES = {
    alarm:        'input_boolean.alarm_actief',
    alarmZone:    'input_text.actieve_zone',
    internet:     'binary_sensor.internet_verbinding',
    speedDown:    'sensor.speedtest_download',
    speedUp:      'sensor.speedtest_upload',
    speedPing:    'sensor.speedtest_ping',
    versieLocal:  'sensor.versie_lokaal',
    nasUpdate:    'update.homenas_update',
    roombaStatus: 'sensor.roomba_status',
    roombaAccu:   'sensor.roomba_accu',
    roombaBakje:  'binary_sensor.roomba_afvalbak_aanwezig',
    roombaVol:    'binary_sensor.roomba_afvalbak_vol',
    deurOpen:     'binary_sensor.nuki_front_door_door_open',
    deurSlot:     'binary_sensor.nuki_front_door_locked',
    deurBat:      'sensor.nuki_front_door_battery',
    tempLiving:   'sensor.woonkamer_temp',
    tempSlaap:    'sensor.slaapkamer_temp',
    tempBureau:   'sensor.bureau_temp',
    tempBuiten:   'sensor.thuis_outdoor_temperature',
  };

  const WASTE_CONFIG = [
    { name: 'RA',     entity: 'sensor.afvalophaling_ra',     icon: '🗑', color: 'darkgrey' },
    { name: 'GFT',    entity: 'sensor.afvalophaling_gft',    icon: '🌿', color: 'green'    },
    { name: 'Papier', entity: 'sensor.afvalophaling_papier', icon: '📦', color: 'goldenrod'},
    { name: 'PMD',    entity: 'sensor.afvalophaling_pmd',    icon: '♻️', color: 'darkblue' },
  ];

  const WASTE_TODAY_TOMORROW = [
    'sensor.container_vandaag',
    'sensor.container_morgen'
  ];

  function updateNetwork(states) {
    const inet = stateVal(states[ENTITIES.internet]) === 'on';
    document.getElementById('net-status').textContent  = inet ? '● VERBONDEN' : '● OFFLINE';
    document.getElementById('net-status').style.color  = inet ? 'var(--ok)' : 'var(--alert)';
    document.getElementById('net-down').textContent    = parseFloat(stateVal(states[ENTITIES.speedDown])).toFixed(0);
    document.getElementById('net-up').textContent      = parseFloat(stateVal(states[ENTITIES.speedUp])).toFixed(0);
    document.getElementById('net-ping').textContent    = parseFloat(stateVal(states[ENTITIES.speedPing])).toFixed(0);
  }

  function updateRoomba(states) {
    const status = stateVal(states[ENTITIES.roombaStatus]);
    const accu   = parseFloat(stateVal(states[ENTITIES.roombaAccu])) || 0;
    const bakje  = stateVal(states[ENTITIES.roombaBakje]) === 'on';
    const vol    = stateVal(states[ENTITIES.roombaVol]) === 'on';

    const statusEl = document.getElementById('roomba-status');
    statusEl.textContent = status;

    let stColor = 'var(--dim)';
    if (status === 'Cleaning') stColor = 'var(--accent)';
    else if (status === 'Charging') stColor = 'var(--ok)';
    else if (status.toLowerCase().includes('error') || status.toLowerCase().includes('fout') || status.toLowerCase().includes('stuck')) stColor = 'var(--alert)';

    statusEl.style.color = stColor;

    const accuEl = document.getElementById('roomba-accu');
    accuEl.textContent = accu;
    accuEl.style.color = accu < 20 ? 'var(--alert)' : accu < 50 ? 'var(--warn)' : 'var(--ok)';

    const bakjeEl = document.getElementById('roomba-bakje');
    bakjeEl.textContent = bakje ? '✔' : '✗';
    bakjeEl.style.color = bakje ? 'var(--ok)' : 'var(--alert)';

    const volEl = document.getElementById('roomba-vol');
    volEl.textContent = vol ? 'VOL' : 'OK';
    volEl.style.color = vol ? 'var(--alert)' : 'var(--ok)';
  }

  function updateDeur(states) {
    const open   = stateVal(states[ENTITIES.deurOpen])  === 'on';
    const locked = stateVal(states[ENTITIES.deurSlot])  === 'off';
    const bat    = parseFloat(stateVal(states[ENTITIES.deurBat])) || 0;

    const openEl = document.getElementById('deur-open');
    openEl.textContent = open ? '● OPEN' : '● DICHT';
    openEl.style.color = open ? 'var(--alert)' : 'var(--ok)';

    const slotEl = document.getElementById('deur-slot');
    slotEl.textContent = locked ? '🔒 VERGRENDELD' : '🔓 OPEN';
    slotEl.style.color = locked ? 'var(--ok)' : 'var(--alert)';

    const batEl = document.getElementById('deur-bat');
    batEl.textContent = `${bat}%`;
    batEl.style.color = bat < 25 ? 'var(--alert)' : bat < 50 ? 'var(--warn)' : 'var(--ok)';
  }

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

  function updateWaste(stateMap) {
    const parseWasteState = (state) => {
      if (!state || state === 'unavailable' || state === 'Geen' || state === 'geen' || state === 'GEEN') {
          return { type: 'GEEN', isImg: false, url: '' };
      }
      if (state.endsWith('.png') || state.endsWith('.jpg') || state.endsWith('.jpeg')) {
          const match = state.match(/\/([^\/]+)\.(png|jpg|jpeg)$/);
          let typeName = 'GEEN';
          if (match && match[1]) {
             typeName = match[1].toUpperCase();
          }
          return { type: typeName, isImg: true, url: state };
      }
      return { type: state.toUpperCase(), isImg: false, url: '' };
    };

    const todayObj = parseWasteState(stateVal(stateMap[WASTE_TODAY_TOMORROW[0]]));
    const tomorrowObj = parseWasteState(stateVal(stateMap[WASTE_TODAY_TOMORROW[1]]));

    const getWasteColor = (type) => {
        if (type === 'GEEN') return '#ccc';
        const config = WASTE_CONFIG.find(w => w.name.toUpperCase() === type.toUpperCase());
        return config ? config.color : '#ccc';
    };

    const todayCard = document.getElementById('wasteTodayCard');
    const todayValEl = document.getElementById('wasteTodayVal');
    const todayNameEl = document.getElementById('wasteTodayName');

    todayNameEl.textContent = todayObj.type;
    todayNameEl.style.color = getWasteColor(todayObj.type);

    if (todayObj.isImg) {
        todayValEl.innerHTML = `<img src="${HA_URL}${todayObj.url}" style="width: 80px; height: 80px; object-fit: contain;">`;
    } else {
        todayValEl.textContent = '';
    }
    todayCard.className = `waste-card waste-card-flex ${todayObj.type !== 'GEEN' ? 'vandaag' : ''}`;

    const tomorrowCard = document.getElementById('wasteTomorrowCard');
    const tomorrowValEl = document.getElementById('wasteTomorrowVal');
    const tomorrowNameEl = document.getElementById('wasteTomorrowName');

    tomorrowNameEl.textContent = tomorrowObj.type;
    tomorrowNameEl.style.color = getWasteColor(tomorrowObj.type);

    if (tomorrowObj.isImg) {
        tomorrowValEl.innerHTML = `<img src="${HA_URL}${tomorrowObj.url}" style="width: 80px; height: 80px; object-fit: contain;">`;
    } else {
        tomorrowValEl.textContent = '';
    }
    tomorrowCard.className = `waste-card waste-card-flex ${tomorrowObj.type !== 'GEEN' ? 'morgen' : ''}`;

    const row = document.getElementById('wasteRow');
    row.innerHTML = '';
    WASTE_CONFIG.forEach(w => {
      const val = stateVal(stateMap[w.entity]);
      let cls = '';
      let when = val;

      if (val === 'Vandaag')    { cls = 'vandaag'; }
      else if (val === 'Morgen') { cls = 'morgen'; }
      else if (val === 'Overmorgen') { cls = 'overmorgen'; }
      else if (val.includes('Binnen') || val.includes('dagen')) { cls = 'binnen'; }

      const card = document.createElement('div');
      card.className = `waste-card ${cls}`;
      card.innerHTML = `
        <span class="waste-icon">${w.icon}</span>
        <span class="waste-name">${w.name}</span>
        <span class="waste-when">${when}</span>`;
      row.appendChild(card);
    });
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
          const url = "https://api.open-meteo.com/v1/forecast?latitude=51.32&longitude=4.95&daily=weathercode,temperature_2m_max,temperature_2m_min&timezone=auto";
          const response = await fetch(url);
          const data = await response.json();
          const container = document.getElementById("weatherRow");
          container.innerHTML = "";

          data.daily.time.forEach((date, index) => {
              const code = data.daily.weathercode[index];
              const weather = weatherCodes[code] || { text: "Onbekend", icon: "https://cdn.jsdelivr.net/gh/basmilius/weather-icons/production/fill/all/not-available.svg" };
              const max = Math.round(data.daily.temperature_2m_max[index]);
              const min = Math.round(data.daily.temperature_2m_min[index]);
              const dayName = new Date(date).toLocaleDateString("nl-BE", { weekday: "short", day: "numeric", month: "short" });

              container.innerHTML += `
                  <div class="weather-card">
                      <div class="weather-day">${dayName}</div>
                      <img class="weather-icon" src="${weather.icon}" alt="${weather.text}">
                      <div class="weather-temp-max">${max}°C</div>
                      <div class="weather-temp-min">${min}°C</div>
                      <div class="weather-description">${weather.text}</div>
                  </div>
              `;
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
      ...Object.values(ENTITIES),
      ...WASTE_CONFIG.map(w => w.entity),
      ...WASTE_TODAY_TOMORROW
    ];

    if (allIds.length > 0) {
      const results = await haGetAll([...new Set(allIds)]);
      const stateMap = {};
      [...new Set(allIds)].forEach((id, i) => stateMap[id] = results[i]);

      updateNetwork(stateMap);
      updateRoomba(stateMap);
      updateDeur(stateMap);
      updateTemp(stateMap);
      updateWaste(stateMap);
    }

    document.getElementById('lastRefresh').textContent =
      'Bijgewerkt: ' + new Date().toLocaleTimeString('nl-BE');
  }

  refresh();
  setInterval(() => { if (!window.isRefreshPaused) refresh(); }, REFRESH);
</script>
</body>
</html>