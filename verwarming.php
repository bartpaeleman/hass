<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="nl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Verwarming</title>
<link href="https://fonts.googleapis.com/css2?family=Share+Tech+Mono&family=Barlow+Condensed:wght@300;400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="CSS/common.css">
<link rel="stylesheet" href="CSS/verwarming.css">
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
      <h1>Verwarming</h1>
      <span>TADO · KLIMAATBEHEER</span>
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

    <div class="section-label">
      <svg width="12" height="12" viewBox="0 0 12 12" fill="none"><path d="M6 1v6.5M6 10a2 2 0 100-4 2 2 0 000 4z" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/><path d="M8 3h1M8 5h1" stroke="currentColor" stroke-width="1" stroke-linecap="round"/></svg>
      Thermostaten
    </div>

    <div class="thermo-grid" id="thermoGrid">
      </div>

    <div class="section-label" style="margin-top:28px;">
      <svg width="12" height="12" viewBox="0 0 12 12" fill="none"><circle cx="6" cy="6" r="4" stroke="currentColor" stroke-width="1.2"/><path d="M6 4v2.5l1.5 1" stroke="currentColor" stroke-width="1" stroke-linecap="round"/></svg>
      Tado Hub
    </div>

    <div class="hub-bar" id="hubBar">
      <div class="hub-stat">
        <span class="hub-stat-label">API Gebruik</span>
        <span class="hub-stat-val" id="hub-api-gebruik">—</span>
      </div>
      <div class="hub-stat">
        <span class="hub-stat-label">API Limiet</span>
        <span class="hub-stat-val" id="hub-api-limiet">—</span>
      </div>
      <div class="hub-stat">
        <span class="hub-stat-label">API Status</span>
        <span class="hub-stat-val" id="hub-api-status">—</span>
      </div>
      <div class="hub-stat">
        <span class="hub-stat-label">Reset</span>
        <span class="hub-stat-val" id="hub-api-reset">—</span>
      </div>
      <div class="hub-stat">
        <span class="hub-stat-label">Zones</span>
        <span class="hub-stat-val" id="hub-zones">—</span>
      </div>
      <div class="hub-stat">
        <span class="hub-stat-label">Laatste sync</span>
        <span class="hub-stat-val" id="hub-sync">—</span>
      </div>
      <div class="hub-stat">
        <span class="hub-stat-label">Warm water</span>
        <span class="hub-stat-val" id="hub-ww">—</span>
      </div>
      <button class="hub-btn" onclick="haButton('button.tado_ce_hub_alle_hervatten')" title="Hervat schema voor alle zones">
        ↺ ALLE HERVATTEN
      </button>
      <button class="hub-btn ww-btn" onclick="haButton('button.warm_water_30min_timer')" title="Warm water 30 min">
        🚿 30 MIN
      </button>
      <button class="hub-btn ww-btn" onclick="haButton('button.warm_water_60min_timer')" title="Warm water 60 min">
        🚿 60 MIN
      </button>
      <button class="hub-btn ww-btn" onclick="haButton('button.warm_water_90min_timer')" title="Warm water 90 min">
        🚿 90 MIN
      </button>
    </div>

  </div>

  <div class="right">
      <?php include 'sidebar.php'; ?>
  </div>
</main>

<div id="toast"></div>

<script>
const REFRESH = 8000;

/* ══════════════════════════════════════
   CONFIGURATIE KAMERS
══════════════════════════════════════ */
const ROOMS = [
  {
    name:       'Woonkamer',
    climate:    'climate.woonkamer',
    tempSensor: 'sensor.woonkamer_temp',
    humidity:   'sensor.woonkamer_vochtigheid',
    heating:    'sensor.woonkamer_verwarming',
    overlay:    'sensor.woonkamer_overlay',
    window:     'binary_sensor.woonkamer_venster',
    battery:    'sensor.tado_batterij_thermostaat',
    boost:      'button.woonkamer_boost',
    smartBoost: 'button.woonkamer_slimme_boost',
    comfort:    'sensor.woonkamer_comfortniveau',
    childlock:  'switch.woonkamer_kinderslot',
  },
  {
    name:       'Badkamer',
    climate:    'climate.badkamer',
    tempSensor: 'sensor.badkamer_temp',
    humidity:   'sensor.badkamer_vochtigheid',
    heating:    'sensor.badkamer_verwarming',
    overlay:    'sensor.badkamer_overlay',
    window:     'binary_sensor.badkamer_venster',
    battery:    'sensor.tado_batterij_badkamer',
    boost:      'button.badkamer_boost',
    smartBoost: 'button.badkamer_slimme_boost',
    comfort:    'sensor.badkamer_comfortniveau',
    childlock:  'switch.badkamer_kinderslot',
  },
  {
    name:       'Bureau',
    climate:    'climate.bureau',
    tempSensor: 'sensor.bureau_temp',
    humidity:   'sensor.bureau_vochtigheid',
    heating:    'sensor.bureau_verwarming',
    overlay:    'sensor.bureau_overlay',
    window:     'binary_sensor.bureau_venster',
    battery:    'sensor.tado_batterij_bureau',
    boost:      'button.bureau_boost',
    smartBoost: 'button.bureau_slimme_boost',
    comfort:    'sensor.bureau_comfortniveau',
    childlock:  'switch.bureau_kinderslot',
  },
  {
    name:       'Slaapkamer',
    climate:    'climate.slaapkamer',
    tempSensor: 'sensor.slaapkamer_temp',
    humidity:   'sensor.slaapkamer_vochtigheid',
    heating:    'sensor.slaapkamer_verwarming',
    overlay:    'sensor.slaapkamer_overlay',
    window:     'binary_sensor.slaapkamer_venster',
    battery:    'sensor.tado_batterij_slaapkamer',
    boost:      'button.slaapkamer_boost',
    smartBoost: 'button.slaapkamer_slimme_boost',
    comfort:    'sensor.slaapkamer_comfortniveau',
    childlock:  'switch.slaapkamer_kinderslot',
  },
  {
    name:       'Kinderkamer',
    climate:    'climate.kinderkamer',
    tempSensor: 'sensor.kinderkamer_temp',
    humidity:   'sensor.kinderkamer_vochtigheid',
    heating:    'sensor.kinderkamer_verwarming',
    overlay:    'sensor.kinderkamer_overlay',
    window:     'binary_sensor.kinderkamer_venster',
    battery:    'sensor.tado_batterij_kinderkamer',
    boost:      'button.kinderkamer_boost',
    smartBoost: 'button.kinderkamer_slimme_boost',
    comfort:    'sensor.kinderkamer_comfortniveau',
    childlock:  'switch.kinderkamer_kinderslot',
  },
  {
    name:       'Gastenkamer',
    climate:    'climate.gastenkamer',
    tempSensor: 'sensor.gastenkamer_temp',
    humidity:   'sensor.gastenkamer_vochtigheid',
    heating:    'sensor.gastenkamer_verwarming',
    overlay:    'sensor.gastenkamer_overlay',
    window:     'binary_sensor.gastenkamer_venster',
    battery:    'sensor.tado_batterij_gastenkamer',
    boost:      'button.gastenkamer_boost',
    smartBoost: 'button.gastenkamer_slimme_boost',
    comfort:    'sensor.gastenkamer_comfortniveau',
    childlock:  'switch.gastenkamer_kinderslot',
  },
  {
    name:       'Gang',
    climate:    'climate.gang',
    tempSensor: 'sensor.gang_temp',
    humidity:   'sensor.gang_vochtigheid',
    heating:    'sensor.gang_verwarming',
    overlay:    'sensor.gang_overlay',
    window:     'binary_sensor.gang_venster',
    battery:    'sensor.tado_batterij_gang',
    boost:      'button.gang_boost',
    smartBoost: 'button.gang_slimme_boost',
    comfort:    'sensor.gang_comfortniveau',
    childlock:  'switch.gang_kinderslot',
  },
  {
    name:       'Toilet',
    climate:    'climate.toilet',
    tempSensor: 'sensor.toilet_temp',
    humidity:   'sensor.toilet_vochtigheid',
    heating:    'sensor.toilet_verwarming',
    overlay:    'sensor.toilet_overlay',
    window:     'binary_sensor.toilet_venster',
    battery:    'sensor.tado_batterij_toilet',
    boost:      'button.toilet_boost',
    smartBoost: 'button.toilet_slimme_boost',
    comfort:    'sensor.toilet_comfortniveau',
    childlock:  'switch.toilet_kinderslot',
  },
];

/* ══════════════════════════════════════
   HA API ACTIES
══════════════════════════════════════ */
function toast(msg, ok = true) {
  const el = document.getElementById('toast');
  el.textContent = msg;
  el.style.borderColor = ok ? 'rgba(0,230,118,0.4)' : 'rgba(255,61,61,0.4)';
  el.style.color = ok ? 'var(--ok)' : 'var(--alert)';
  el.classList.add('show');
  clearTimeout(window._toastTimer);
  window._toastTimer = setTimeout(() => el.classList.remove('show'), 2800);
}

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

async function haButton(entityId) {
  const ok = await haCall('button', 'press', { entity_id: entityId });
  if (ok) toast('✓ ' + entityId.split('.')[1].replace(/_/g,' ').toUpperCase());
}

async function setTemperature(climateId, temp) {
  const ok = await haCall('climate', 'set_temperature', {
    entity_id: climateId,
    temperature: temp
  });
  if (ok) toast(`✓ ${climateId.split('.')[1].toUpperCase()} → ${temp}°C`);
}

async function toggleSwitch(entityId) {
  const ok = await haCall('switch', 'toggle', { entity_id: entityId });
  if (ok) toast('✓ SLOT GEWIJZIGD');
  setTimeout(refresh, 1200);
}

async function setMode(climateId, mode) {
  const ok = await haCall('climate', 'set_hvac_mode', {
    entity_id: climateId,
    hvac_mode: mode
  });
  if (ok) toast(`✓ ${climateId.split('.')[1].toUpperCase()} → ${mode.toUpperCase()}`);
  setTimeout(refresh, 1200);
}

/* ══════════════════════════════════════
   COMFORT KLEUR HELPER
══════════════════════════════════════ */
function comfortStyle(val) {
  if (!val || val === 'unavailable') return 'color:var(--dim)';
  const v = val.toLowerCase();
  if (v === 'ok' || v.includes('comfortable') || v.includes('comfortabel')) return 'color:var(--ok)';
  if (v.includes('warm') || v.includes('hot'))  return 'color:var(--alert)';
  if (v.includes('cold') || v.includes('koud')) return 'color:#00ccff';
  return 'color:var(--text)';
}

/* ══════════════════════════════════════
   BATTERIJ KLEUR HELPER
══════════════════════════════════════ */
function battStyle(val) {
  if (!val || val === 'unavailable') return 'color:var(--dim)';
  const v = val.toLowerCase();
  if (v === 'normal' || v === 'normaal' || v === 'ok' || v === 'good') return 'color:var(--ok)';
  return 'color:var(--alert)';
}

/* ══════════════════════════════════════
   KAART RENDER
══════════════════════════════════════ */
const localTargets = {};

function renderCard(container, room, stateMap) {
  const id = room.climate.replace('.', '_');

  const climateState = stateMap[room.climate] || {};
  const attrs        = climateState.attributes || {};
  const hvacMode     = attrs.hvac_mode   || 'heat';
  const hvacAction   = attrs.hvac_action || 'idle';
  const serverTarget = parseFloat(attrs.temperature);

  if (!(id in localTargets) && !isNaN(serverTarget)) localTargets[id] = serverTarget;
  else if (!(id in localTargets)) localTargets[id] = 20; // fallback
  const targetTemp = localTargets[id];

  const currentTemp = parseFloat(stateVal(stateMap[room.tempSensor])) || 18;
  const humid       = stateVal(stateMap[room.humidity]);
  const heatingPct  = stateVal(stateMap[room.heating]);
  const overlayVal  = stateVal(stateMap[room.overlay]);
  const windowOpen  = stateVal(stateMap[room.window]) === 'on';
  const bat         = stateVal(stateMap[room.battery]);
  let comfort       = stateVal(stateMap[room.comfort]);
  const lockIsOn    = stateVal(stateMap[room.childlock]) === 'on';

  if (comfort && (comfort.toLowerCase() === 'comfortable' || comfort.toLowerCase() === 'comfortabel')) {
      comfort = 'OK';
  }

  const isOff     = stateVal(climateState) === 'off' || hvacMode === 'off';
  const isTargetUnknown = attrs.temperature == null || String(attrs.temperature).toLowerCase() === 'unknown' || String(attrs.temperature).toLowerCase() === 'unavailable';
  const isHeating = hvacAction === 'heating';

  const targetText = (isTargetUnknown || isOff) ? 'Uit' : targetTemp.toFixed(1) + '°C';

  let statusClass = 'status-ok';
  if (!isOff && isHeating) {
    statusClass = 'status-heating';
  }

  const actualColor = getComfortColor(currentTemp, room.name);

  // ── Badges ──
  const hasOverlay = overlayVal && overlayVal !== 'none' && overlayVal !== 'unavailable' && overlayVal !== 'None';

  let badges = '';
  if (isHeating) badges += `<span class="thermo-badge badge-heating">● VERWARMT</span>`;
  else if (isOff) badges += `<span class="thermo-badge badge-off">UIT</span>`;
  else            badges += `<span class="thermo-badge badge-idle">STAND-BY</span>`;
  if (windowOpen)  badges += `<span class="thermo-badge badge-window">🪟 VENSTER</span>`;
  if (hasOverlay && !isOff)  badges += `<span class="thermo-badge badge-overlay">HANDMATIG</span>`;

  // ── Mode button classes ──
  const modeHeat = (hvacMode === 'heat' && isHeating) ? 'active-heat' : (hvacMode === 'heat' ? 'active-heat-idle' : '');
  const modeAuto = (hvacMode === 'auto') ? 'active-auto' : '';
  const modeOff  = (isOff)  ? 'active-off'  : '';

  let card = document.getElementById(`tc-${id}`);
  if (!card) {
    card = document.createElement('div');
    card.id = `tc-${id}`;
    container.appendChild(card);
  }

  card.className = `thermo-card ${statusClass}`;

  card.innerHTML = `
    <div class="thermo-visuals" style="display:flex; flex-direction:column; align-items:center; gap:8px; flex-shrink:0; width:140px;">

      <div class="thermo-target-ctrl-new" style="display:flex; align-items:center; background:var(--bg); border:1px solid var(--border); border-radius:5px; overflow:hidden; flex-shrink:0; width:100%; justify-content:space-between;">
        <button class="thermo-temp-btn" onclick="adjTemp('${id}','${room.climate}',-0.5)">−</button>
        <span class="thermo-target-val" id="tval-${id}" onclick="setMidTemp('${id}', '${room.climate}', '${room.name}')" style="min-width:36px; text-align:center; font-family:'Share Tech Mono', monospace; font-size:16px; color:var(--text); cursor:pointer;">${targetText}</span>
        <button class="thermo-temp-btn" onclick="adjTemp('${id}','${room.climate}',+0.5)">+</button>
      </div>

      <div style="margin-top: 0px; margin-bottom: 0px; position:relative; width: 120px; height: 75px;">
        <svg width="120" height="75" viewBox="0 0 120 75" style="overflow:visible; position:absolute; top:0; left:0;">
          <path d="M 10 60 A 50 50 0 0 1 110 60" fill="none" stroke="rgba(255,255,255,0.05)" stroke-width="8" stroke-linecap="round" />

          <path d="M 10 60 A 50 50 0 0 1 110 60" fill="none" stroke="${isOff ? '#888888' : 'var(--ok)'}" stroke-width="8" stroke-linecap="round"
                stroke-dasharray="${Math.min(157.08, (targetTemp/40)*157.08)} 157.08" />

          <path d="M 22 60 A 38 38 0 0 1 98 60" fill="none" stroke="rgba(255,255,255,0.05)" stroke-width="8" stroke-linecap="round" />

          <path d="M 22 60 A 38 38 0 0 1 98 60" fill="none" stroke="${actualColor}" stroke-width="8" stroke-linecap="round"
                stroke-dasharray="${Math.min(119.38, (currentTemp/40)*119.38)} 119.38" />
        </svg>

        <div style="position:absolute; top: 66px; left: 6px; font-family:'Share Tech Mono', monospace; font-size:9px; color:#a0a8b5;">0</div>
        <div style="position:absolute; top: -6px; left: 56px; font-family:'Share Tech Mono', monospace; font-size:9px; color:#a0a8b5;">20</div>
        <div style="position:absolute; top: 66px; right: 6px; font-family:'Share Tech Mono', monospace; font-size:9px; color:#a0a8b5;">40</div>
      </div>

      <div style="width:100%; text-align:center; font-family:'Share Tech Mono', monospace; font-size:22px; font-weight:bold; color:${actualColor};">
        ${Math.round(currentTemp * 10) / 10} °C
      </div>

      <div class="thermo-actions" style="display:flex; gap:5px; justify-content:center; width:100%;">
         ${room.boost      ? `<button class="action-btn boost-btn" onclick="haButton('${room.boost}')">⚡</button>` : ''}
         ${room.smartBoost ? `<button class="action-btn"           onclick="haButton('${room.smartBoost}')">🧠</button>` : ''}
         ${room.childlock  ? `<button class="action-btn ${lockIsOn ? 'active-lock' : ''}" onclick="toggleSwitch('${room.childlock}')">${lockIsOn ? '🔒' : '🔓'}</button>` : ''}
      </div>

    </div>

    <div class="thermo-info" style="display:flex; flex-direction:column; justify-content:space-between; height:100%; width:100%;">

      <div style="display:flex; flex-direction:column; align-items:flex-start; margin-bottom: 12px; gap: 8px;">
        <div class="thermo-name" style="margin-bottom:0; max-width: 100%; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${room.name}</div>
      </div>

      <div class="thermo-mode-row" style="margin-bottom:12px;">
        <button class="mode-btn ${modeHeat}" onclick="setMode('${room.climate}','heat')">🔥 MAN</button>
        <button class="mode-btn ${modeAuto}" onclick="setMode('${room.climate}','auto')">📅 AUTO</button>
        <button class="mode-btn ${modeOff}"  onclick="setMode('${room.climate}','off')">○ UIT</button>
      </div>

      <div class="thermo-stats-custom" style="display:grid; grid-template-columns: 1fr 1fr; gap:8px;">
        <div class="thermo-stat">
          <span class="thermo-stat-label">Verwarming</span>
          <span class="thermo-stat-val">${heatingPct !== 'unavailable' ? heatingPct + '%' : '—'}</span>
        </div>
        <div class="thermo-stat">
          <span class="thermo-stat-label">Batterij</span>
          <span class="thermo-stat-val" style="${battStyle(bat)}; font-size: 13px;">${bat !== 'unavailable' ? bat : '—'}</span>
        </div>
        <div class="thermo-stat">
          <span class="thermo-stat-label">Comfort</span>
          <span class="thermo-stat-val" style="${comfortStyle(comfort)}; font-size: 13px;">${comfort !== 'unavailable' ? comfort : '—'}</span>
        </div>
        <div class="thermo-stat">
          <span class="thermo-stat-label">Vocht</span>
          <span class="thermo-stat-val">${humid !== 'unavailable' ? humid + '%' : '—'}</span>
        </div>
      </div>

      <div style="display:flex; justify-content:flex-end; align-items:flex-end; margin-top:8px;">
         <div class="thermo-badges" style="display:flex; gap:5px; flex-wrap:wrap;">
            ${badges}
         </div>
      </div>

    </div>`;
}

const adjTimers = {};
function adjTemp(id, climateId, delta) {
  localTargets[id] = Math.round((localTargets[id] + delta) * 2) / 2;
  localTargets[id] = Math.max(5, Math.min(30, localTargets[id]));
  const el = document.getElementById(`tval-${id}`);
  if (el) el.textContent = localTargets[id].toFixed(1) + '°C';

  clearTimeout(adjTimers[id]);
  adjTimers[id] = setTimeout(() => {
    setTemperature(climateId, localTargets[id]);
  }, 800);
}

function setMidTemp(id, climateId, roomName) {
  const bounds = COMFORT_BOUNDARIES[roomName] || {min: 20.0, max: 22.0};
  const min = bounds.min;
  const max = bounds.max;
  if (min && max) {
    localTargets[id] = Math.round(((min + max) / 2) * 2) / 2;
  } else {
    localTargets[id] = 21.0;
  }
  const el = document.getElementById(`tval-${id}`);
  if (el) el.textContent = localTargets[id].toFixed(1) + '°C';

  clearTimeout(adjTimers[id]);
  adjTimers[id] = setTimeout(() => {
    setTemperature(climateId, localTargets[id]);
  }, 800);
}

/* ══════════════════════════════════════
   HUB UPDATE
══════════════════════════════════════ */
const HUB_ENTITIES = {
  'sensor.tado_ce_hub_api_gebruik':           'hub-api-gebruik',
  'sensor.tado_ce_hub_api_limiet':            'hub-api-limiet',
  'sensor.tado_ce_hub_api_status':            'hub-api-status',
  'sensor.tado_ce_hub_api_opnieuw_instellen': 'hub-api-reset',
  'sensor.tado_ce_hub_zonetelling':           'hub-zones',
  'sensor.tado_ce_hub_laatste_synchronisatie':'hub-sync',
  'sensor.warm_water_overlay':                'hub-ww',
};

function updateHub(stateMap) {
  Object.entries(HUB_ENTITIES).forEach(([entity, elId]) => {
    const el = document.getElementById(elId);
    if (!el) return;
    const val = stateVal(stateMap[entity]);

    if (val === 'unavailable') { el.textContent = '—'; return; }

    if (entity === 'sensor.tado_ce_hub_api_status') {
      el.textContent = val;
      el.className = 'hub-stat-val ' + (val.toLowerCase() === 'ok' ? 'ok' : 'alert');
      return;
    }

    if (entity === 'sensor.tado_ce_hub_api_gebruik') {
      const gebruik = parseFloat(val) || 0;
      const limiet  = parseFloat(stateVal(stateMap['sensor.tado_ce_hub_api_limiet'])) || 1;
      const pct = gebruik / limiet;
      el.textContent = val;
      el.className = 'hub-stat-val ' + (pct > 0.85 ? 'alert' : pct > 0.6 ? 'warn' : 'ok');
      return;
    }

    if (entity.includes('synchronisatie') || entity.includes('instellen')) {
      try {
        const d = new Date(val);
        el.textContent = d.toLocaleTimeString('nl-BE', { hour:'2-digit', minute:'2-digit' });
      } catch { el.textContent = val; }
      return;
    }

    el.textContent = val;
    el.className = 'hub-stat-val';
  });
}

/* ══════════════════════════════════════
   MAIN REFRESH
══════════════════════════════════════ */
async function refresh() {
  const roomEntityIds = ROOMS.flatMap(r => [
    r.climate, r.tempSensor, r.humidity, r.heating,
    r.overlay, r.window, r.battery, r.comfort, r.childlock
  ].filter(Boolean));

  const allIds = [...new Set([...roomEntityIds, ...Object.keys(HUB_ENTITIES),
    'sensor.tado_ce_hub_api_limiet'
  ])];

  const results = await haGetAll(allIds);
  const stateMap = {};
  allIds.forEach((id, i) => stateMap[id] = results[i]);

  updateHub(stateMap);

  const grid = document.getElementById('thermoGrid');
  ROOMS.forEach(room => renderCard(grid, room, stateMap));

  document.getElementById('lastRefresh').textContent =
    'Bijgewerkt: ' + new Date().toLocaleTimeString('nl-BE');
}

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