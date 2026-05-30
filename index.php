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


    <div class="energy-grid" style="margin-top: 24px;">
        <div class="col-12" id="tvPauzeContainer" style="display:none; text-align: center;">
            <div id="tvPauzeStartSection" style="padding: 20px; border: 2px solid grey; background: rgb(95,95,95,0.1); border-radius: 8px; cursor: pointer;">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom: 10px; color: grey;">
                    <rect x="2" y="7" width="20" height="15" rx="2" ry="2"></rect>
                    <polyline points="17 2 12 7 7 2"></polyline>
                </svg>
                <div style="font-size: 18px; font-weight: bold; color: var(--text);">START TV PAUZE</div>
            </div>

            <div id="tvPauzeStopSection" style="display: none; padding: 20px; border: 4px solid red; background: transparent; border-radius: 8px; height: 100%; box-sizing: border-box; flex-direction: column; justify-content: center; align-items: center;">
                <div id="tvPauzeTimer" style="font-size: 10rem; font-weight: bold; line-height: 1; margin: 20px 0; color: var(--text);">00:00</div>
                <div id="tvPauzeStopBtn" style="background-color: #f44336; color: white; border-radius: 25px; padding: 20px; width: 100%; cursor: pointer; display: flex; justify-content: center; align-items: center; gap: 10px;">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="currentColor">
                        <rect x="6" y="6" width="12" height="12"></rect>
                    </svg>
                    <span style="font-size: 2.5rem; font-weight: bold;">STOP TV PAUZE</span>
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
    tempLiving:   'sensor.woonkamer_temp',
    tempSlaap:    'sensor.slaapkamer_temp',
    tempBureau:   'sensor.bureau_temp',
    tempBuiten:   'sensor.thuis_outdoor_temperature',
    tvPauzeScript:'script.tvpauze',
    tvPauzeTimer: 'timer.tv_pauze'
  };

  let tvPauzeInterval = null;

  function updateTvPauze(states) {
    const scriptState = stateVal(states[ENTITIES.tvPauzeScript]);
    const timerStateObj = states[ENTITIES.tvPauzeTimer];
    const container = document.getElementById('tvPauzeContainer');
    const startSection = document.getElementById('tvPauzeStartSection');
    const stopSection = document.getElementById('tvPauzeStopSection');
    const timerEl = document.getElementById('tvPauzeTimer');

    container.style.display = 'block';

    if (tvPauzeInterval) {
        clearInterval(tvPauzeInterval);
        tvPauzeInterval = null;
    }

    if (scriptState === 'off') {
        startSection.style.display = 'block';
        stopSection.style.display = 'none';
        container.style.height = 'auto';
    } else {
        startSection.style.display = 'none';
        stopSection.style.display = 'flex';
        container.style.height = '60vh';

        if (timerStateObj && timerStateObj.state === 'active' && timerStateObj.attributes && timerStateObj.attributes.finishes_at) {
            const finishesAt = new Date(timerStateObj.attributes.finishes_at).getTime();

            const updateTimer = () => {
                const now = new Date().getTime();
                let remaining = finishesAt - now;

                if (remaining <= 0) {
                    timerEl.textContent = '00:00';
                    clearInterval(tvPauzeInterval);
                    return;
                }

                const totalSeconds = Math.floor(remaining / 1000);
                const m = Math.floor(totalSeconds / 60).toString().padStart(2, '0');
                const s = (totalSeconds % 60).toString().padStart(2, '0');
                timerEl.textContent = `${m}:${s}`;
            };

            updateTimer();
            tvPauzeInterval = setInterval(updateTimer, 1000);
        } else {
             timerEl.textContent = '00:00';
        }
    }
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
      ...Object.values(ENTITIES)
    ];

    if (allIds.length > 0) {
      const results = await haGetAll([...new Set(allIds)]);
      const stateMap = {};
      [...new Set(allIds)].forEach((id, i) => stateMap[id] = results[i]);

      updateTemp(stateMap);
      updateTvPauze(stateMap);
    }

    document.getElementById('lastRefresh').textContent =
      'Bijgewerkt: ' + new Date().toLocaleTimeString('nl-BE');
  }

  refresh();
  setInterval(() => { if (!window.isRefreshPaused) refresh(); }, REFRESH);

  document.addEventListener('DOMContentLoaded', () => {
      const startBtn = document.getElementById('tvPauzeStartSection');

      if (startBtn) {
          startBtn.addEventListener('click', async () => {
              try {
                  await haPost('script', '1765569524531', ''); // The user specified this script ID
                  refresh();
              } catch (e) {
                  console.error("Failed to start TV pause", e);
              }
          });
      }

      const actualStopBtn = document.getElementById('tvPauzeStopBtn');
      if (actualStopBtn) {
          actualStopBtn.addEventListener('click', async () => {
              try {
                  await haPost('script', 'tvpauze_stop', '');
                  refresh();
              } catch (e) {
                  console.error("Failed to stop TV pause", e);
              }
          });
      }
  });
</script>
</body>
</html>