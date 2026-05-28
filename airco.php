<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="nl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Airco Dashboard</title>
<link href="https://fonts.googleapis.com/css2?family=Share+Tech+Mono&family=Barlow+Condensed:wght@300;400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="CSS/common.css">
<link rel="stylesheet" href="CSS/airco.css">
</head>
<body>

<header>
  <div class="logo">
    <div class="logo-icon"></div>
    <div>
      <h1>Airco</h1>
      <span>KLIMAAT · DAIKIN</span>
    </div>
  </div>
  <div class="header-right">
    <div class="live-badge"><div class="live-dot"></div> LIVE</div>
    <div class="clock" id="lastRefresh" style="font-size:10px;margin-right:8px;"></div>
    <div class="clock" id="clock">--:--:--</div>
  </div>
</header>

<main>
  <!-- ══ LEFT ══ -->
  <div class="left">

    <!-- Summary Bar -->
    <div class="ac-summary-bar" id="acSummaryBar">
      <div class="ac-summary-stat">
        <span class="ac-summary-label">Actief</span>
        <span class="ac-summary-val" id="sum-active">—</span>
      </div>
      <div class="ac-summary-stat">
        <span class="ac-summary-label">Koelen</span>
        <span class="ac-summary-val cool" id="sum-cooling">—</span>
      </div>
      <div class="ac-summary-stat">
        <span class="ac-summary-label">Verwarmen</span>
        <span class="ac-summary-val heat" id="sum-heating">—</span>
      </div>
      <div class="ac-summary-stat">
        <span class="ac-summary-label">Totaal vermogen</span>
        <span class="ac-summary-val" id="sum-power">—</span>
      </div>
      <div class="ac-summary-stat" style="display:flex; align-items:center; justify-content:center; cursor:pointer;" onclick="toggleAutoAirco('input_boolean.autoairco')">
        <span class="ac-auto-btn" id="sum-auto-btn" style="width:100%; height:100%; font-size:14px;">AUTO AIRCO</span>
      </div>
    </div>

    <!-- Section heading -->
    <div class="section-label">
      <svg width="12" height="12" viewBox="0 0 12 12" fill="none"><path d="M6 1v2M6 9v2M1 6h2M9 6h2M2.7 2.7l1.4 1.4M7.9 7.9l1.4 1.4M2.7 9.3l1.4-1.4M7.9 4.1l1.4-1.4" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/></svg>
      Airco Units
    </div>

    <div class="airco-grid" id="aircoGrid">
      <!-- Injected by JS -->
    </div>

  </div>

  <!-- ══ RIGHT sidebar ══ -->
  <div class="right">
    <?php include 'sidebar.php'; ?>
  </div>
</main>

<!-- Toast -->
<div id="toast" style="
  position:fixed; bottom:24px; left:50%; transform:translateX(-50%) translateY(20px);
  background:var(--surface); border:1px solid var(--border); border-radius:6px;
  padding:10px 20px; font-family:'Share Tech Mono',monospace; font-size:12px;
  color:var(--text); opacity:0; transition:opacity 0.3s, transform 0.3s;
  z-index:500; pointer-events:none; white-space:nowrap;">
</div>

<script>
// ════════════════════════════════════════════════
//  ⚙️  CONFIGURATIE
// ════════════════════════════════════════════════
const REFRESH = 5000;

// Airco units — koppelt Daikin AC climate entity aan Daikin Onecta sensors
const AC_UNITS = [
  {
    name:      'Living',
    climate:   'climate.living_airco',
    power:     'switch.living_power',
    streamer:  'switch.living_streamer',
    tempIn:    'sensor.living_inside_temperature',
    tempOut:   'sensor.living_outside_temperature',
    compFreq:  'sensor.living_compressor_frequency',
    compPower: 'sensor.living_compressor_estimated_power_consumption',
    energy:    'sensor.living_energy_consumption',
    monthHeat: 'sensor.living_climatecontrol_maandelijks_elektriciteitsverbruik_verwarmen',
    monthCool: 'sensor.living_climatecontrol_maandelijks_elektriciteitsverbruik_koelen',
    powerful:  null,
    autoAirco: 'input_boolean.auto_airco_living'
  },
  {
    name:      'Eetkamer',
    climate:   'climate.eetkamer_airco',
    power:     'switch.eetkamer_power',
    streamer:  'switch.eetkamer_streamer',
    tempIn:    'sensor.eetkamer_inside_temperature',
    tempOut:   'sensor.eetkamer_outside_temperature',
    compFreq:  'sensor.eetkamer_compressor_frequency',
    compPower: 'sensor.eetkamer_compressor_estimated_power_consumption',
    energy:    'sensor.eetkamer_energy_consumption',
    monthHeat: 'sensor.eetkamer_climatecontrol_maandelijks_elektriciteitsverbruik_verwarmen',
    monthCool: 'sensor.eetkamer_climatecontrol_maandelijks_elektriciteitsverbruik_koelen',
    powerful:  null,
    autoAirco: 'input_boolean.auto_airco_living'
  },
  {
    name:      'Bureau',
    climate:   'climate.bureau_airco',
    power:     'switch.bureau_power',
    streamer:  'switch.bureau_streamer',
    tempIn:    'sensor.bureau_inside_temperature',
    tempOut:   'sensor.bureau_outside_temperature',
    compFreq:  'sensor.bureau_compressor_frequency',
    compPower: 'sensor.bureau_compressor_estimated_power_consumption',
    energy:    'sensor.bureau_energy_consumption',
    monthHeat: 'sensor.bureau_climatecontrol_maandelijks_elektriciteitsverbruik_verwarmen',
    monthCool: 'sensor.bureau_climatecontrol_maandelijks_elektriciteitsverbruik_koelen',
    powerful:  null,
    autoAirco: 'input_boolean.auto_airco_bureau'
  },
  {
    name:      'Slaapkamer',
    climate:   'climate.slaapkamer_airco',
    power:     'switch.slaapkamer_power',
    streamer:  'switch.slaapkamer_streamer',
    tempIn:    'sensor.slaapkamer_inside_temperature',
    tempOut:   'sensor.slaapkamer_outside_temperature',
    compFreq:  'sensor.slaapkamer_compressor_frequency',
    compPower: 'sensor.slaapkamer_compressor_estimated_power_consumption',
    energy:    'sensor.slaapkamer_energy_consumption',
    monthHeat: 'sensor.slaapkamer_climatecontrol_maandelijks_elektriciteitsverbruik_verwarmen',
    monthCool: 'sensor.slaapkamer_climatecontrol_maandelijks_elektriciteitsverbruik_koelen',
    powerful:  null,
    autoAirco: 'input_boolean.auto_airco_slaapkamer'
  },
];

// Fan speed opties per Daikin modus (HA fan_mode strings)
const FAN_SPEEDS = ['Auto', '1', '2', '3', '4', '5'];

// HVAC modus → CSS class mapping
const MODE_CLASS = {
  cool: 'ac-cool',
  heat: 'ac-heat',
  fan_only: 'ac-fan',
  dry: 'ac-dry',
  off: 'ac-off',
  auto: 'ac-cool', // treat auto as cool visually
};

// Lokale doeltemperatuuren (voor debounce)
const localTargets = {};
const adjTimers    = {};

// ════════════════════════════════════════════════
//  SVG DIAL HELPERS
// ════════════════════════════════════════════════

function modeColor(mode) {
  if (mode === 'cool')     return '#00b4d8';
  if (mode === 'heat')     return '#ff8c42';
  if (mode === 'fan_only') return '#00e676';
  if (mode === 'dry')      return '#ffb300';
  if (mode === 'auto')     return '#48cae4';
  return '#3a4554';
}

function buildDialSVG(targetTemp, mode, modeLabel, unitIdx = 'TEXT', actualTemp = null) {
  const tColor = modeColor(mode);
  const aColor = mode === 'heat' ? '#ff8c42' : '#00e676';

  const minTemp = 0;
  const maxTemp = 40;
  const arcDeg = 220;
  const startAngle = 160;

  // Vergroot de radius
  const tRadius = 76;
  const tCirc = 2 * Math.PI * tRadius;
  const tArcLen = (arcDeg / 360) * tCirc;
  const tPct = Math.max(0, Math.min(1, (targetTemp - minTemp) / (maxTemp - minTemp)));
  const tFilledLen = (mode === 'off') ? 0 : (tPct * tArcLen);

  const aRadius = 90;
  const aCirc = 2 * Math.PI * aRadius;
  const aArcLen = (arcDeg / 360) * aCirc;
  const aVal = (actualTemp !== null && !isNaN(actualTemp)) ? actualTemp : 0;
  const aPct = Math.max(0, Math.min(1, (aVal - minTemp) / (maxTemp - minTemp)));
  const aFilledLen = aPct * aArcLen;

  const displayTemp = (mode === 'off') ? '—' : targetTemp.toFixed(1);

  return `
    <svg viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
      <g transform="rotate(5 100 100)">
        <circle class="dial-track" cx="100" cy="100" r="${tRadius}"
          stroke-dasharray="${tArcLen} 999" stroke-dashoffset="0"
          transform="rotate(${startAngle} 100 100)" stroke-width="12" />
        <circle class="dial-range" cx="100" cy="100" r="${tRadius}"
          stroke="${tColor}" stroke-dasharray="${tFilledLen} 999" stroke-dashoffset="0"
          transform="rotate(${startAngle} 100 100)" stroke-width="12" />

        <circle class="dial-track" cx="100" cy="100" r="${aRadius}"
          stroke-dasharray="${aArcLen} 999" stroke-dashoffset="0"
          transform="rotate(${startAngle} 100 100)" stroke-width="8" />
        <circle class="dial-range" cx="100" cy="100" r="${aRadius}"
          stroke="${aColor}" stroke-dasharray="${aFilledLen} 999" stroke-dashoffset="0"
          transform="rotate(${startAngle} 100 100)" stroke-width="8" />
      </g>
      <text x="100" y="105" text-anchor="middle" class="dial-temp-val" id="dial-val-${unitIdx}">${displayTemp}</text>
      <text x="100" y="125" text-anchor="middle" class="dial-temp-unit">°C</text>
      <text x="100" y="145" text-anchor="middle" class="dial-mode-label">${modeLabel}</text>
    </svg>`;
}

// ════════════════════════════════════════════════
//  TOAST
// ════════════════════════════════════════════════
function toast(msg, ok = true) {
  const el = document.getElementById('toast');
  el.textContent = msg;
  el.style.borderColor = ok ? 'rgba(0,230,118,0.4)' : 'rgba(255,61,61,0.4)';
  el.style.color = ok ? 'var(--ok)' : 'var(--alert)';
  el.style.opacity = '1';
  el.style.transform = 'translateX(-50%) translateY(0)';
  clearTimeout(window._toast);
  window._toast = setTimeout(() => {
    el.style.opacity = '0';
    el.style.transform = 'translateX(-50%) translateY(20px)';
  }, 2800);
}

// ════════════════════════════════════════════════
//  HA API CALLS
// ════════════════════════════════════════════════
async function haCall(domain, service, data) {
  try {
    const r = await fetch(`${HA_URL}/api/services/${domain}/${service}`, {
      method: 'POST',
      headers: { Authorization: `Bearer ${HA_TOKEN}`, 'Content-Type': 'application/json' },
      body: JSON.stringify(data)
    });
    if (!r.ok) throw new Error(r.status);
    return true;
  } catch (e) {
    toast('Fout: ' + e.message, false);
    return false;
  }
}

async function toggleAutoAirco(entityId) {
  const ok = await haCall('input_boolean', 'toggle', { entity_id: entityId });
  if (ok) toast(`✓ AUTO AIRCO TOGGLED`);
  setTimeout(refresh, 1000);
}

async function setPower(unit, on) {
  const svc = on ? 'turn_on' : 'turn_off';
  const ok  = await haCall('switch', svc, { entity_id: unit.power });
  if (ok) toast(`✓ ${unit.name.toUpperCase()} — ${on ? 'AAN' : 'UIT'}`);
  setTimeout(refresh, 1000);
}

async function setMode(unit, mode) {
  const ok = await haCall('climate', 'set_hvac_mode', {
    entity_id: unit.climate,
    hvac_mode: mode
  });
  if (ok) toast(`✓ ${unit.name.toUpperCase()} → ${mode.toUpperCase()}`);
  setTimeout(refresh, 1200);
}

async function setTemperature(unit, temp) {
  const ok = await haCall('climate', 'set_temperature', {
    entity_id: unit.climate,
    temperature: temp
  });
  if (ok) toast(`✓ ${unit.name.toUpperCase()} → ${temp}°C`);
}

async function setFanSpeed(unit, speed) {
  const ok = await haCall('climate', 'set_fan_mode', {
    entity_id: unit.climate,
    fan_mode: speed
  });
  if (ok) toast(`✓ ${unit.name.toUpperCase()} fan → ${speed}`);
  setTimeout(refresh, 1000);
}

async function toggleStreamer(unit) {
  const ok = await haCall('switch', 'toggle', { entity_id: unit.streamer });
  if (ok) toast(`✓ ${unit.name.toUpperCase()} streamer`);
  setTimeout(refresh, 1000);
}

// Temperatuur aanpassen met debounce
function adjTemp(unitIdx, delta) {
  const unit = AC_UNITS[unitIdx];
  const id   = 'ac_' + unitIdx;
  if (!(id in localTargets)) localTargets[id] = 22;
  localTargets[id] = Math.round((localTargets[id] + delta) * 2) / 2;
  localTargets[id] = Math.max(16, Math.min(30, localTargets[id]));

  // Update dial en getal direct
  const valEl  = document.getElementById(`dial-val-${unitIdx}`);
  const svgEl  = document.getElementById(`dial-svg-${unitIdx}`);
  const card   = document.getElementById(`ac-card-${unitIdx}`);
  const mode   = card ? card.dataset.mode : 'cool';
  const actual = card ? parseFloat(card.dataset.actual) : null;

  // Because we moved text inside SVG, we rebuild the whole SVG
  const modeLabels = { cool: 'KOELEN', heat: 'VERWARMEN', fan_only: 'VENTILATIE', dry: 'ONTVOCHTIGEN', auto: 'AUTO', off: 'UIT' };
  const mLabel = modeLabels[mode] || mode.toUpperCase();
  if (svgEl) svgEl.innerHTML = buildDialSVG(localTargets[id], mode, mLabel, unitIdx, actual);

  clearTimeout(adjTimers[id]);
  adjTimers[id] = setTimeout(() => {
    setTemperature(unit, localTargets[id]);
  }, 800);
}

// ════════════════════════════════════════════════
//  RENDER / UPDATE CARDS
// ════════════════════════════════════════════════
function fmtVal(obj, decimals = 1, unit = '') {
  if (!obj || obj.state === 'unavailable' || obj.state === 'unknown') return '—';
  const n = parseFloat(obj.state);
  if (isNaN(n)) return obj.state;
  return n.toFixed(decimals) + (unit ? ` ${unit}` : '');
}

function renderAcCard(container, unitIdx, stateMap) {
  const unit = AC_UNITS[unitIdx];
  const id   = 'ac_' + unitIdx;

  const climateObj  = stateMap[unit.climate]  || {};
  const attrs       = climateObj.attributes   || {};
  const hvacMode    = climateObj.state || 'off'; // 'cool','heat','fan_only','dry','off','auto'
  const hvacAction  = attrs.hvac_action || 'idle';
  const serverTemp  = parseFloat(attrs.temperature);
  const fanMode     = attrs.fan_mode || 'Auto';

  // Sync lokale doeltemp pas als er geen pending timer loopt
  if (!(adjTimers[id]) && !isNaN(serverTemp)) localTargets[id] = serverTemp;
  else if (!(id in localTargets)) localTargets[id] = 22;
  const targetTemp = localTargets[id];

  const isOff      = hvacMode === 'off';
  const isOn       = !isOff;
  const cardClass  = isOff ? 'ac-off' : (MODE_CLASS[hvacMode] || 'ac-cool');

  const tempIn   = fmtVal(stateMap[unit.tempIn],  1, '°C');
  const tempOut  = fmtVal(stateMap[unit.tempOut], 1, '°C');
  const compFreq = fmtVal(stateMap[unit.compFreq], 0, 'Hz');
  const compPow  = fmtVal(stateMap[unit.compPower], 0, 'W');
  const energy   = fmtVal(stateMap[unit.energy],  2, 'kWh');

  const mHeat = fmtVal(stateMap[unit.monthHeat], 1, 'kWh');
  const mCool = fmtVal(stateMap[unit.monthCool], 1, 'kWh');

  const streamerOn = unit.streamer && stateVal(stateMap[unit.streamer]) === 'on';
  const autoAircoOn = stateVal(stateMap[unit.autoAirco]) === 'on';

  // Mode knoppen: active class
  const modeMap = {
    cool:     hvacMode === 'cool'     ? 'active-cool' : '',
    heat:     hvacMode === 'heat'     ? 'active-heat' : '',
    fan_only: hvacMode === 'fan_only' ? 'active-fan'  : '',
    dry:      hvacMode === 'dry'      ? 'active-dry'  : '',
    auto:     hvacMode === 'auto'     ? 'active-auto' : '',
    off:      isOff                   ? 'active-off'  : '',
  };

  // Modus label
  const modeLabels = {
    cool: 'KOELEN', heat: 'VERWARMEN', fan_only: 'VENTILATIE',
    dry: 'ONTVOCHTIGEN', auto: 'AUTO', off: 'UIT'
  };
  const modeLabel = modeLabels[hvacMode] || hvacMode.toUpperCase();

  // Fan indicator
  const fanIcon = (hvacMode === 'fan_only') ? '<span class="fan-spin">⟳</span>' : '⟳';

  let wrapper = document.getElementById(`ac-wrap-${unitIdx}`);
  if (!wrapper) {
    wrapper = document.createElement('div');
    wrapper.id = `ac-wrap-${unitIdx}`;
    wrapper.className = 'ac-card-wrapper';
    container.appendChild(wrapper);
  }

  let card = document.getElementById(`ac-card-${unitIdx}`);
  if (!card) {
    card = document.createElement('div');
    card.id = `ac-card-${unitIdx}`;
    wrapper.appendChild(card);
  }
  card.className = `ac-card ${cardClass}`;
  card.dataset.mode = hvacMode;
  card.dataset.actual = tempIn.replace(' °C', '');

  card.innerHTML = `
    <!-- Header -->
    <div class="ac-header">
      <div>
        <div class="ac-room-name">${unit.name}</div>
      </div>
      <div class="ac-header-btns">
        <button class="ac-auto-btn ${autoAircoOn ? 'auto-on' : ''}" onclick="toggleAutoAirco('${unit.autoAirco}')">
          AUTO
        </button>
        <button class="ac-power-btn" onclick="setPower(AC_UNITS[${unitIdx}], ${isOff})" title="${isOff ? 'Inschakelen' : 'Uitschakelen'}">⏻</button>
      </div>
    </div>

    <div class="ac-body-layout">
      <!-- Modus knoppen verticaal -->
      <div class="ac-modes-vertical">
        <button class="ac-mode-btn ${modeMap.cool}" onclick="setMode(AC_UNITS[${unitIdx}], 'cool')">
          <span class="mode-icon">❄️</span>KOEL
        </button>
        <button class="ac-mode-btn ${modeMap.heat}" onclick="setMode(AC_UNITS[${unitIdx}], 'heat')">
          <span class="mode-icon">🔥</span>WARM
        </button>
        <button class="ac-mode-btn ${modeMap.fan_only}" onclick="setMode(AC_UNITS[${unitIdx}], 'fan_only')">
          <span class="mode-icon">💨</span>FAN
        </button>
        <button class="ac-mode-btn ${modeMap.dry}" onclick="setMode(AC_UNITS[${unitIdx}], 'dry')">
          <span class="mode-icon">💧</span>DRY
        </button>
        <button class="ac-mode-btn ${modeMap.auto}" onclick="setMode(AC_UNITS[${unitIdx}], 'auto')">
          <span class="mode-icon">🔄</span>AUTO
        </button>
      </div>

      <div class="ac-main-content">
        <!-- Dial -->
        <div class="ac-dial-section">
          <button class="dial-adj-btn minus" onclick="adjTemp(${unitIdx}, -0.5)">−</button>
          <div class="ac-dial-wrap" id="dial-svg-${unitIdx}">
            ${buildDialSVG(targetTemp, hvacMode, modeLabel, unitIdx, parseFloat(tempIn))}
          </div>
          <button class="dial-adj-btn plus" onclick="adjTemp(${unitIdx}, +0.5)">+</button>
        </div>

        <!-- Binnentemperatuur / buitentemperatuur -->
        <div class="ac-temps-row">
          <div class="ac-temp-item">
            <span class="ac-temp-label">Binnen</span>
            <span class="ac-temp-value">${tempIn}</span>
          </div>
          <div style="width:1px; background:var(--border);"></div>
          <div class="ac-temp-item">
            <span class="ac-temp-label">Buiten</span>
            <span class="ac-temp-value">${tempOut}</span>
          </div>
        </div>

        <!-- Fan speed -->
        <div class="ac-fan-row">
          <span class="ac-fan-label">⟳ FAN</span>
          <div class="ac-fan-btns">
            ${FAN_SPEEDS.map(s => `
              <button class="ac-fan-btn ${fanMode.toLowerCase() === s.toLowerCase() ? 'active-fan-speed' : ''}"
                      onclick="setFanSpeed(AC_UNITS[${unitIdx}], '${s}')">${s}</button>
            `).join('')}
          </div>
        </div>
      </div>
    </div>

  `;
}

// ════════════════════════════════════════════════
//  SUMMARY BAR
// ════════════════════════════════════════════════
function updateSummary(stateMap) {
  let activeCount = 0, coolingCount = 0, heatingCount = 0, totalPower = 0;

  AC_UNITS.forEach(unit => {
    const climateObj = stateMap[unit.climate] || {};
    const mode       = climateObj.state || 'off';
    const action     = (climateObj.attributes || {}).hvac_action || '';
    if (mode !== 'off') activeCount++;
    if (action === 'cooling') coolingCount++;
    if (action === 'heating') heatingCount++;

    const p = parseFloat((stateMap[unit.compPower] || {}).state);
    if (!isNaN(p)) totalPower += p;
  });

  document.getElementById('sum-active').textContent  = activeCount;
  document.getElementById('sum-cooling').textContent = coolingCount;
  document.getElementById('sum-heating').textContent = heatingCount;
  document.getElementById('sum-power').textContent   = totalPower.toFixed(0) + ' W';

  const globalAutoOn = stateVal(stateMap['input_boolean.autoairco']) === 'on';
  const sumAutoBtn = document.getElementById('sum-auto-btn');
  if (sumAutoBtn) {
    if (globalAutoOn) {
      sumAutoBtn.classList.add('auto-on');
    } else {
      sumAutoBtn.classList.remove('auto-on');
    }
  }
}

// ════════════════════════════════════════════════
//  MAIN REFRESH
// ════════════════════════════════════════════════
async function refresh() {
  const allIds = AC_UNITS.flatMap(u => [
    u.climate, u.power, u.streamer, u.tempIn, u.tempOut,
    u.compFreq, u.compPower, u.energy, u.monthHeat, u.monthCool, u.autoAirco
  ].filter(Boolean));
  allIds.push('input_boolean.autoairco');

  const results = await haGetAll([...new Set(allIds)]);
  const stateMap = {};
  [...new Set(allIds)].forEach((id, i) => stateMap[id] = results[i]);

  const grid = document.getElementById('aircoGrid');

  // Render all units
  AC_UNITS.forEach((unit, idx) => {
    if (unit) renderAcCard(grid, idx, stateMap);
  });

  updateSummary(stateMap);

  document.getElementById('lastRefresh').textContent =
    'Bijgewerkt: ' + new Date().toLocaleTimeString('nl-BE');
}

// ── Clock ──
function tick() {
  document.getElementById('clock').textContent =
    new Date().toLocaleTimeString('nl-BE', { hour12: false });
}
tick();
setInterval(tick, 1000);

refresh();
setInterval(() => { if (!window.isRefreshPaused) refresh(); }, REFRESH);
</script>
</body>
</html>