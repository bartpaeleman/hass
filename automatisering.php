<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="nl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Automatisering</title>
<link href="https://fonts.googleapis.com/css2?family=Share+Tech+Mono&family=Barlow+Condensed:wght@300;400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="CSS/automatisering.css">
<link rel="stylesheet" href="CSS/verlichting.css">
<link rel="stylesheet" href="CSS/energie.css">
<link rel="stylesheet" href="CSS/common.css">
<script>
  const PAGE_MIN_ACTION_LEVEL = 50;    
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
      <h1>Automatisering</h1>
      <span>DASHBOARD</span>
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

    <div class="top-sections-wrapper">
    <details id="details-scripts" class="auto-section" style="margin-bottom:0;">
      <summary class="energy-subtitle">
        <span>🤖</span> SCRIPTS
      </summary>
      <div class="auto-section-content">
        <div class="auto-grid">
        <div id="tvPauzeContainer" style="display:none; text-align: center; display: flex; flex-direction: column;">
            <div id="tvPauzeStartSection" style="padding: 20px; border: 2px solid grey; background: rgb(95,95,95,0.1); border-radius: 8px; cursor: pointer; flex-grow: 1; display: flex; flex-direction: column; justify-content: center; align-items: center;">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom: 10px; color: grey;">
                    <rect x="2" y="7" width="20" height="15" rx="2" ry="2"></rect>
                    <polyline points="17 2 12 7 7 2"></polyline>
                </svg>
                <div style="font-size: 18px; font-weight: bold; color: var(--text);">START TV PAUZE</div>
            </div>

            <div id="tvPauzeStopSection" style="display: none; padding: 20px; border: 4px solid red; background: transparent; border-radius: 8px; box-sizing: border-box; flex-direction: column; justify-content: center; align-items: center; flex-grow: 1;">
                <div id="tvPauzeTimer" style="font-size: 4rem; font-weight: bold; line-height: 1; margin: 10px 0; color: var(--text);">00:00</div>
                <div id="tvPauzeStopBtn" style="background-color: #f44336; color: white; border-radius: 25px; padding: 15px; width: 100%; cursor: pointer; display: flex; justify-content: center; align-items: center; gap: 10px;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                        <rect x="6" y="6" width="12" height="12"></rect>
                    </svg>
                    <span style="font-size: 1.5rem; font-weight: bold;">STOP TV</span>
                </div>
            </div>
        </div>

        <div id="lichtEtenBtn" style="padding: 20px; border: 2px solid grey; background: rgb(95,95,95,0.1); border-radius: 8px; cursor: pointer; display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center; transition: all 0.2s;">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom: 10px;" id="lichtEtenIcon">
                <circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
            </svg>
            <div style="font-size: 18px; font-weight: bold; color: var(--text);" id="lichtEtenText">LICHT ETEN AAN</div>
        </div>
      </div>
    </details>

    <details id="details-wekker" class="auto-section" style="margin-bottom:0;">
      <summary class="energy-subtitle">
        <span>⏰</span> WEKKER
      </summary>
      <div class="auto-section-content">
        <!-- WEKKER AAN / UIT -->
        <div id="wekkerMainContainer" class="wekker-main-toggle wekker-off">
            <div class="wekker-title">
                <span class="wekker-icon">💡</span> WEKKER
                <span id="wekkerTimeDisplay" style="margin-left: 8px; font-family: 'Share Tech Mono', monospace; font-size: 16px; color: var(--warn); background: rgba(0,0,0,0.3); padding: 2px 8px; border-radius: 4px;">--:--</span>
            </div>
            <label class="switch">
              <input type="checkbox" id="wekkertoggle" onchange="toggleWekker('input_boolean.wekker')">
              <span class="slider"></span>
            </label>
        </div>

        <!-- WEKKER ALARM NOTIFICATIONS -->
        <div class="wekker-row">
            <div class="wekker-label">Lampen</div>
            <select id="wekkerKeuzeSelect" class="wekker-select" onchange="setWekkerSelect('input_select.wekkerkeuze', this.value)">
                <option value="Bart">Bart</option>
                <option value="Linda">Linda</option>
                <option value="Beiden">Beiden</option>
            </select>
        </div>

        <div class="wekker-switches-grid">
            <div class="wekker-row">
                <div class="wekker-label">Aankondiging Voice</div>
                <label class="switch">
                  <input type="checkbox" id="wekkerAankondigingToggle" onchange="toggleWekker('input_boolean.wekker_aankondiging')">
                  <span class="slider"></span>
                </label>
            </div>

            <div class="wekker-row">
                <div class="wekker-label">Licht Auto-Aan</div>
                <label class="switch">
                  <input type="checkbox" id="wekkerLichtToggle" onchange="toggleWekker('input_boolean.wekker_licht')">
                  <span class="slider"></span>
                </label>
            </div>
        </div>

        <!-- WEKKER DATETIME -->
        <div class="wekker-row">
            <div class="wekker-label">Tijdstip Alarm</div>
            <input type="time" id="wekkerTijdInput" class="wekker-time" onchange="setWekkerTime('input_datetime.tijd_alarm', this.value)">
        </div>
      </div>
    </details>
    </div> <!-- end .top-sections-wrapper -->

    <details id="details-energie" class="auto-section">
      <summary class="energy-subtitle">
        <span>📊</span> Energie Intelligentie
      </summary>
      <div class="auto-section-content">
        <div class="energy-grid">
        <div class="energy-card compact-card col-4">
            <div class="energy-card-header">
                <div class="energy-icon green" style="width:28px;height:28px;font-size:14px;">⚡</div>
                <div class="energy-title">Overschot</div>
            </div>
            <div class="energy-value" id="val-airco-overschot">—</div>
        </div>
        <div class="energy-card airco-card col-4" id="card-airco-auto" onclick="toggleAirco('input_boolean.autoairco')">
            <div class="energy-card-header">
                <div class="energy-icon" id="icon-airco-auto">❄️</div>
                <div class="energy-title">AUTO AIRCO</div>
            </div>
            <div class="energy-value" id="val-airco-auto">—</div>
        </div>
        <div class="energy-card compact-card col-4">
            <div class="energy-card-header">
                <div class="energy-icon yellow" style="width:28px;height:28px;font-size:14px;">🏠</div>
                <div class="energy-title">Verbruik</div>
            </div>
            <div class="energy-value" id="val-huisverbruik">—</div>
        </div>
    </div>
    <div class="energy-grid">
        <div class="energy-card airco-card col-4" id="card-airco-living" onclick="toggleAirco('input_boolean.auto_airco_living')">
            <div class="energy-card-header">
                <div class="energy-icon" id="icon-airco-living">❄️</div>
                <div class="energy-title">LIVING</div>
            </div>
            <div class="energy-value" id="val-airco-living">—</div>
        </div>
        <div class="energy-card airco-card col-4" id="card-airco-slaap" onclick="toggleAirco('input_boolean.auto_airco_slaapkamer')">
            <div class="energy-card-header">
                <div class="energy-icon" id="icon-airco-slaap">❄️</div>
                <div class="energy-title">SLAAPKAMER</div>
            </div>
            <div class="energy-value" id="val-airco-slaap">—</div>
        </div>
        <div class="energy-card airco-card col-4" id="card-airco-bureau" onclick="toggleAirco('input_boolean.auto_airco_bureau')">
            <div class="energy-card-header">
                <div class="energy-icon" id="icon-airco-bureau">❄️</div>
                <div class="energy-title">BUREAU</div>
            </div>
            <div class="energy-value" id="val-airco-bureau">—</div>
        </div>
      </div>
    </details>

    <details id="details-lichten" class="auto-section">
      <summary class="energy-subtitle">
        <span>💡</span> AUTOLICHTEN
      </summary>
      <div class="auto-section-content">
        <div class="auto-grid">
        <div class="light-card light-off" id="card-avondlichten" onclick="toggleLight('input_boolean.avondlichten')">
            <div class="light-card-info">
            <div class="light-icon" id="icon-avondlichten">💡</div>
            <div class="light-title">Avondlichten</div>
            </div>
            <div class="light-value" id="val-avondlichten">UIT</div>
        </div>

        <div class="light-card light-off" id="card-portaal-licht" onclick="toggleLight('input_boolean.portaal_licht')">
            <div class="light-card-info">
                <div class="light-icon" id="icon-portaal-licht">💡</div>
                <div class="light-title">Portaal</div>
            </div>
            <div class="light-value" id="val-portaal-licht">UIT</div>
        </div>

        <div class="light-card light-off" id="card-koepel-licht" onclick="toggleLight('input_boolean.koepel_licht')">
            <div class="light-card-info">
                <div class="light-icon" id="icon-koepel-licht">💡</div>
                <div class="light-title">Koepel</div>
            </div>
            <div class="light-value" id="val-koepel-licht">UIT</div>
        </div>

        <div class="light-card light-off" id="card-kerstkrans-switch" onclick="toggleLight('input_boolean.kerstkrans_switch')">
            <div class="light-card-info">
                <div class="light-icon" id="icon-kerstkrans-switch">💡</div>
                <div class="light-title">Kerstkrans</div>
            </div>
            <div class="light-value" id="val-kerstkrans-switch">UIT</div>
        </div>

        <div class="light-card light-off" id="card-auto-licht-garage" onclick="toggleLight('input_boolean.auto_licht_garage_kerst')">
            <div class="light-card-info">
                <div class="light-icon" id="icon-auto-licht-garage">💡</div>
                <div class="light-title">Garage</div>
            </div>
            <div class="light-value" id="val-auto-licht-garage">UIT</div>
        </div>

        <div class="light-card light-off" id="card-plantentuin-licht" onclick="toggleLight('input_boolean.plantentuin_licht')">
            <div class="light-card-info">
                <div class="light-icon" id="icon-plantentuin-licht">💡</div>
                <div class="light-title">Plantentuin</div>
            </div>
            <div class="light-value" id="val-plantentuin-licht">UIT</div>
        </div>

        <div class="light-card light-off" id="card-achtertuin-licht" onclick="toggleLight('input_boolean.achtertuin_licht')">
            <div class="light-card-info">
                <div class="light-icon" id="icon-achtertuin-licht">💡</div>
                <div class="light-title">Tuinhuis</div>
            </div>
            <div class="light-value" id="val-achtertuin-licht">UIT</div>
        </div>
      </div>
    </details>

  </div>

  <div class="right">

      <?php include 'sidebar.php'; ?>
  </div>
</main>


<script>
  const REFRESH = 3000;

  function tick() {
    document.getElementById('clock').textContent = new Date().toLocaleTimeString('nl-BE', { hour12: false });
  }
  tick();
  setInterval(tick, 1000);

  const ENTITIES = {
    tvPauzeScript: 'script.tvpauze',
    tvPauzeTimer:  'timer.tv_pauze',
    lichtEten:     'light.zetel_bart_links',

    aircoAuto:     'input_boolean.autoairco',
    aircoLiving:   'input_boolean.auto_airco_living',
    aircoSlaap:    'input_boolean.auto_airco_slaapkamer',
    aircoBureau:   'input_boolean.auto_airco_bureau',
    aircoOverschot:'sensor.airco_solar_eligible',
    huisverbruik:  'sensor.huisverbruik_totaal',

    wekker:              'input_boolean.wekker',
    wekkerKeuze:         'input_select.wekkerkeuze',
    wekkerAankondiging:  'input_boolean.wekker_aankondiging',
    wekkerLicht:         'input_boolean.wekker_licht',
    wekkerTijd:          'input_datetime.tijd_alarm'
  };

  const LIGHT_ENTITIES = [
    { id: 'input_boolean.avondlichten', cardId: 'card-avondlichten', valId: 'val-avondlichten', onClass: 'light-alert' },
    { id: 'input_boolean.portaal_licht', cardId: 'card-portaal-licht', valId: 'val-portaal-licht', onClass: 'light-warn' },
    { id: 'input_boolean.koepel_licht', cardId: 'card-koepel-licht', valId: 'val-koepel-licht', onClass: 'light-ok' },
    { id: 'input_boolean.kerstkrans_switch', cardId: 'card-kerstkrans-switch', valId: 'val-kerstkrans-switch', onClass: 'light-ok' },
    { id: 'input_boolean.auto_licht_garage_kerst', cardId: 'card-auto-licht-garage', valId: 'val-auto-licht-garage', onClass: 'light-ok' },
    { id: 'input_boolean.plantentuin_licht', cardId: 'card-plantentuin-licht', valId: 'val-plantentuin-licht', onClass: 'light-ok' },
    { id: 'input_boolean.achtertuin_licht', cardId: 'card-achtertuin-licht', valId: 'val-achtertuin-licht', onClass: 'light-ok' }
  ];

  let tvPauzeInterval = null;

  function stateVal(obj) { return obj ? obj.state : '—'; }

  const formatVal = (stateObj) => {
    if (!stateObj || stateObj.state === 'unavailable') return '—';
    let val = stateObj.state;
    if (!isNaN(parseFloat(val))) val = parseFloat(val).toFixed(2);
    return val + (stateObj.attributes?.unit_of_measurement ? ` <span class="energy-unit">${stateObj.attributes.unit_of_measurement}</span>` : '');
  };

  function updateLichtEten(states) {
      const lichtObj = states[ENTITIES.lichtEten];
      const btn = document.getElementById('lichtEtenBtn');
      const icon = document.getElementById('lichtEtenIcon');
      const textEl = document.getElementById('lichtEtenText');

      if (!lichtObj || !btn) return;

      if (lichtObj.state === 'on') {
          btn.style.borderColor = 'var(--ok)';
          btn.style.backgroundColor = 'rgba(0, 230, 118, 0.15)';
          icon.style.color = 'var(--ok)';
          textEl.textContent = 'LICHT ETEN UIT';
          textEl.style.color = 'var(--text-bright)';
      } else {
          btn.style.borderColor = 'grey';
          btn.style.backgroundColor = 'rgba(95, 95, 95, 0.1)';
          icon.style.color = 'grey';
          textEl.textContent = 'LICHT ETEN AAN';
          textEl.style.color = 'var(--text)';
      }
  }

  function updateTvPauze(states) {
    const scriptState = stateVal(states[ENTITIES.tvPauzeScript]);
    const timerStateObj = states[ENTITIES.tvPauzeTimer];
    const container = document.getElementById('tvPauzeContainer');
    const startSection = document.getElementById('tvPauzeStartSection');
    const stopSection = document.getElementById('tvPauzeStopSection');
    const timerEl = document.getElementById('tvPauzeTimer');

    if(!container) return;

    container.style.display = 'flex';

    if (tvPauzeInterval) {
        clearInterval(tvPauzeInterval);
        tvPauzeInterval = null;
    }

    const isTimerActive = (timerStateObj && timerStateObj.state === 'active');
    const isScriptRunning = (scriptState !== 'off' && scriptState !== '—');

    if (!isScriptRunning && !isTimerActive) {
        startSection.style.display = 'flex';
        stopSection.style.display = 'none';
        container.style.height = 'auto';
        container.style.gridColumn = 'auto';
    } else {
        startSection.style.display = 'none';
        stopSection.style.display = 'flex';
        container.style.height = '100%';
        container.style.gridColumn = '1 / -1';

        if (isTimerActive && timerStateObj.attributes && timerStateObj.attributes.finishes_at) {
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

  async function toggleAirco(entityId) {
      try {
          const r = await fetch(`${HA_URL}/api/services/input_boolean/toggle`, {
              method: 'POST',
              headers: {
                Authorization: `Bearer ${HA_TOKEN}`,
                'Content-Type': 'application/json'
              },
              body: JSON.stringify({ entity_id: entityId })
          });
          if (!r.ok) throw new Error(`HA Service error: ${r.status}`);
          setTimeout(refresh, 500);
      } catch (err) {
          console.error('Failed to toggle airco', err);
      }
  }

  async function toggleLight(entityId) {
    const domain = entityId.split('.')[0];
    try {
        const r = await fetch(`${HA_URL}/api/services/${domain}/toggle`, {
            method: 'POST',
            headers: {
              Authorization: `Bearer ${HA_TOKEN}`,
              'Content-Type': 'application/json'
            },
            body: JSON.stringify({ entity_id: entityId })
        });
        if (!r.ok) throw new Error(`HA Service error: ${r.status}`);
        setTimeout(refresh, 500);
    } catch (err) {
        console.error('Failed to toggle', err);
    }
  }

  async function toggleWekker(entityId) {
      try {
          await haPost('input_boolean', 'toggle', entityId);
          setTimeout(refresh, 500);
      } catch (err) {
          console.error('Failed to toggle wekker boolean', err);
      }
  }

  async function setWekkerSelect(entityId, optionStr) {
      try {
          await haPost('input_select', 'select_option', entityId, { option: optionStr });
          setTimeout(refresh, 500);
      } catch (err) {
          console.error('Failed to set wekker select', err);
      }
  }

  async function setWekkerTime(entityId, timeStr) {
      try {
          await haPost('input_datetime', 'set_datetime', entityId, { time: timeStr });
          setTimeout(refresh, 500);
      } catch (err) {
          console.error('Failed to set wekker time', err);
      }
  }

  async function refresh() {
    const allIds = [
      ...Object.values(ENTITIES),
      ...LIGHT_ENTITIES.map(l => l.id)
    ];

    if (allIds.length > 0) {
      const results = await haGetAll([...new Set(allIds)]);
      const stateMap = {};
      [...new Set(allIds)].forEach((id, i) => stateMap[id] = results[i]);

      updateTvPauze(stateMap);
      updateLichtEten(stateMap);

      const aircos = [
        { id: 'aircoAuto', cardId: 'card-airco-auto', valId: 'val-airco-auto' },
        { id: 'aircoLiving', cardId: 'card-airco-living', valId: 'val-airco-living' },
        { id: 'aircoSlaap', cardId: 'card-airco-slaap', valId: 'val-airco-slaap' },
        { id: 'aircoBureau', cardId: 'card-airco-bureau', valId: 'val-airco-bureau' }
      ];

      aircos.forEach(airco => {
        const obj = stateMap[ENTITIES[airco.id]];
        const card = document.getElementById(airco.cardId);
        const valEl = document.getElementById(airco.valId);

        if (obj && obj.state !== 'unavailable') {
            const isOn = obj.state === 'on';
            if (valEl) valEl.textContent = isOn ? 'AAN' : 'UIT';
            if (card) {
                card.className = `energy-card airco-card col-4 ${isOn ? 'airco-on' : 'airco-off'}`;
            }
        } else {
            if (valEl) valEl.textContent = '—';
            if (card) card.className = 'energy-card airco-card col-4 airco-off';
        }
      });

      const elOverschot = document.getElementById('val-airco-overschot');
      if (elOverschot) elOverschot.innerHTML = formatVal(stateMap[ENTITIES.aircoOverschot]);

      const elHuis = document.getElementById('val-huisverbruik');
      if (elHuis) elHuis.innerHTML = formatVal(stateMap[ENTITIES.huisverbruik]);

      // Wekker updates
      const wekkerObj = stateMap[ENTITIES.wekker];
      if (wekkerObj) {
          const isOn = wekkerObj.state === 'on';
          const container = document.getElementById('wekkerMainContainer');
          const checkbox = document.getElementById('wekkertoggle');
          if (container) {
              container.className = 'wekker-main-toggle ' + (isOn ? 'wekker-on' : 'wekker-off');
          }
          if (checkbox) checkbox.checked = isOn;
      }

      const wekkerAankObj = stateMap[ENTITIES.wekkerAankondiging];
      if (wekkerAankObj) {
          const cb = document.getElementById('wekkerAankondigingToggle');
          if (cb) cb.checked = (wekkerAankObj.state === 'on');
      }

      const wekkerLichtObj = stateMap[ENTITIES.wekkerLicht];
      if (wekkerLichtObj) {
          const cb = document.getElementById('wekkerLichtToggle');
          if (cb) cb.checked = (wekkerLichtObj.state === 'on');
      }

      const wekkerKeuzeObj = stateMap[ENTITIES.wekkerKeuze];
      if (wekkerKeuzeObj && wekkerKeuzeObj.state !== 'unavailable') {
          const sel = document.getElementById('wekkerKeuzeSelect');
          if (sel) sel.value = wekkerKeuzeObj.state;
      }

      const wekkerTijdObj = stateMap[ENTITIES.wekkerTijd];
      if (wekkerTijdObj && wekkerTijdObj.state !== 'unavailable') {
          const tm = document.getElementById('wekkerTijdInput');
          const td = document.getElementById('wekkerTimeDisplay');
          // HA returns HH:MM:SS, time input expects HH:MM (though it tolerates HH:MM:SS usually)
          if (wekkerTijdObj.state.length >= 5) {
              const val = wekkerTijdObj.state.substring(0,5);
              if (tm) tm.value = val;
              if (td) td.textContent = val;
          }
      }

      LIGHT_ENTITIES.forEach(le => {
        const obj = stateMap[le.id];
        if (!obj) return;
        const isOn = (obj.state === 'on');

        if (le.cardId) {
          const card = document.getElementById(le.cardId);
          if (card) {
            card.className = `light-card ${isOn ? 'light-on ' + (le.onClass||'') : 'light-off'}`;
          }
        }
        if (le.valId) {
          const valEl = document.getElementById(le.valId);
          if (valEl) {
            valEl.textContent = isOn ? 'AAN' : 'UIT';
          }
        }
      });
    }

    document.getElementById('lastRefresh').textContent = 'Bijgewerkt: ' + new Date().toLocaleTimeString('nl-BE');
  }

  refresh();
  setInterval(() => { if (!window.isRefreshPaused) refresh(); }, REFRESH);

  document.addEventListener('DOMContentLoaded', () => {
      const startBtn = document.getElementById('tvPauzeStartSection');
      if (startBtn) {
          startBtn.addEventListener('click', async () => {
              try {
                  await haPost('script', '1765569524531', '');
                  refresh();
              } catch (e) { console.error(e); }
          });
      }

      const actualStopBtn = document.getElementById('tvPauzeStopBtn');
      if (actualStopBtn) {
          actualStopBtn.addEventListener('click', async () => {
              try {
                  await haPost('script', 'tvpauze_stop', '');
                  refresh();
              } catch (e) { console.error(e); }
          });
      }

      const lichtBtn = document.getElementById('lichtEtenBtn');
      if (lichtBtn) {
          lichtBtn.addEventListener('click', async () => {
              const textEl = document.getElementById('lichtEtenText');
              const isAan = textEl.textContent.includes('UIT');
              try {
                  if (isAan) {
                      await haPost('light', 'turn_off', ENTITIES.lichtEten);
                  } else {
                      await haPost('script', 'etenstijd_lichten', '');
                  }
                  refresh();
              } catch(e) { console.error(e); }
          });
      }

      // LocalStorage for auto-section details elements
      document.querySelectorAll('.auto-section').forEach(details => {
          const id = details.id;
          if (!id) return;
          const lsKey = 'auto_section_' + id;
          const state = localStorage.getItem(lsKey);

          if (state === 'open') {
              details.open = true;
          } else if (state === 'closed') {
              details.open = false;
          } else {
              // Default if no storage found: default is closed in HTML, so do nothing.
              details.open = false;
          }

          details.addEventListener('toggle', (e) => {
              localStorage.setItem(lsKey, details.open ? 'open' : 'closed');
          });
      });
  });
</script>

</body>
</html>
