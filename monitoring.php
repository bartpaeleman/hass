<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="nl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Monitoring Dashboard</title>
<link href="https://fonts.googleapis.com/css2?family=Share+Tech+Mono&family=Barlow+Condensed:wght@300;400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="CSS/common.css">
<link rel="stylesheet" href="CSS/monitoring.css">
<link rel="stylesheet" href="CSS/verlichting.css">
<link rel="stylesheet" href="CSS/automatisering.css">
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
      <h1>Monitoring</h1>
      <span>THUISSYSTEEM · BEVEILIGINGSCENTRUM</span>
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

    <!-- Top Banners -->
    <div class="top-banners">
      <div class="alarm-banner ok" id="alarmBanner">
        <div class="alarm-left">
          <div class="alarm-icon">✔</div>
          <div>
            <div class="alarm-title">Alles OK</div>
            <div class="alarm-sub" id="alarmSub">Geen actieve meldingen</div>
          </div>
        </div>
        <div style="font-size:11px;letter-spacing:2px;color:var(--dim);font-family:'Share Tech Mono',monospace;">
          ALARM ACTIEF<br>
          <span style="color:var(--muted);font-size:10px;">Klik om te wisselen</span>
        </div>
      </div>

      <div class="versions-banner" id="versionsBanner">
        <div style="font-size:10px; color:var(--dim); font-weight:600; letter-spacing:3px;">VERSIES</div>
        <div class="version-mini-item">
          <span style="color:var(--muted)">LOKAAL</span>
          <span id="ver-local" style="font-family:'Share Tech Mono', monospace; color:var(--text)">—</span>
        </div>
        <div class="version-mini-item">
          <span style="color:var(--muted)">NAS</span>
          <span id="ver-nas" style="font-family:'Share Tech Mono', monospace;">—</span>
        </div>
      </div>
    </div>




    <!-- Persons -->
    <?php if (isset($_SESSION['role_level']) && $_SESSION['role_level'] >= 50): ?>
    <details id="details-aanwezigheid" class="auto-section" style="margin-top: 24px;">
      <summary class="energy-subtitle">
        <svg width="12" height="12" viewBox="0 0 12 12" fill="none" style="margin-right: 8px;"><circle cx="6" cy="4" r="2.5" stroke="currentColor" stroke-width="1.2"/><path d="M1 11c0-2.8 2.2-5 5-5s5 2.2 5 5" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/></svg>
        Aanwezigheid
      </summary>
      <div class="auto-section-content">
        <div class="persons-grid" id="personsGrid">
          <!-- injected by JS -->
        </div>
      </div>
    </details>
    <?php endif; ?>

    <!-- Sensors -->
    <details id="details-sensoren" class="auto-section">
      <summary class="energy-subtitle">
        <svg width="12" height="12" viewBox="0 0 12 12" fill="none" style="margin-right: 8px;"><circle cx="6" cy="6" r="5" stroke="currentColor" stroke-width="1.5"/><circle cx="6" cy="6" r="2" fill="currentColor"/></svg>
        Bewegingssensoren
      </summary>
      <div class="auto-section-content">
        <!-- Gecombineerde Sensor Grid -->
        <div class="sensor-grid" id="sensorGrid">
          <!-- cards injected by JS -->
        </div>
      </div>
    </details>

    <!-- Alarm Notificaties -->
    <details id="details-alarm-notificaties" class="auto-section">
      <summary class="energy-subtitle" id="alarmNotificatiesSummary" style="display:flex; justify-content:space-between; align-items:center; transition: all 0.3s ease;">
        <div style="display:flex; align-items:center;">
            <svg width="12" height="12" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5" style="margin-right: 8px;"><path d="M6 1C3 1 1 3 1 6v3l-1 1v1h12v-1l-1-1V6c0-3-2-5-5-5zm0 11c-1.1 0-2-.9-2-2h4c0 1.1-.9 2-2 2z"/></svg>
            ALARM NOTIFICATIES
        </div>
        <div onclick="event.stopPropagation();" style="display:flex; align-items:center;">
            <label class="switch">
              <input type="checkbox" id="toggleAlarmHeader" onchange="toggleBoolean('input_boolean.alarm')">
              <span class="slider"></span>
            </label>
        </div>
      </summary>
      <div class="auto-section-content">
        <!-- ROW 1: Toggles -->
        <div class="alarm-grid-3">
            <div class="wekker-row">
                <div class="wekker-label">ACTIVATIE PRESENCE FAKER</div>
                <label class="switch">
                  <input type="checkbox" id="toggleFakePresence" onchange="toggleBoolean('input_boolean.fakepresence')">
                  <span class="slider"></span>
                </label>
            </div>
            <div class="wekker-row">
                <div class="wekker-label">Alarmen naar Bart</div>
                <label class="switch">
                  <input type="checkbox" id="toggleNotifyBart" onchange="toggleBoolean('input_boolean.notify_bart')">
                  <span class="slider"></span>
                </label>
            </div>
        </div>

        <!-- ROW 2: Zones -->
        <div class="energy-subtitle" style="margin-top: 24px; border-bottom: none; font-size: 11px;">BEWEGINGSDETECTIE ZONES</div>
        <div class="alarm-grid-3">
            <div class="light-card light-off" id="card-zone-beneden" onclick="toggleBoolean('input_boolean.beweging_detectie_beneden')">
                <div class="light-card-info">
                    <div class="light-icon">📍</div>
                    <div class="light-title">Beneden</div>
                </div>
            </div>
            <div class="light-card light-off" id="card-zone-boven" onclick="toggleBoolean('input_boolean.beweging_detectie_boven')">
                <div class="light-card-info">
                    <div class="light-icon">📍</div>
                    <div class="light-title">Boven</div>
                </div>
            </div>
            <div class="light-card light-off" id="card-zone-buiten" onclick="toggleBoolean('input_boolean.beweging_detectie_buiten')">
                <div class="light-card-info">
                    <div class="light-icon">📍</div>
                    <div class="light-title">Buiten</div>
                </div>
            </div>
        </div>

        <!-- ROW 3: Presence -->
        <div class="energy-subtitle" style="margin-top: 24px; border-bottom: none; font-size: 11px;">AANWEZIGHEID IN HUIS</div>
        <div class="auto-grid">
            <div class="light-card light-off" id="card-pres-all" onclick="toggleBoolean('input_boolean.aanwezig')">
                <div class="light-card-info">
                    <div class="light-icon">🏠</div>
                    <div class="light-title">Aanwezig</div>
                </div>
            </div>
            <div class="light-card light-off" id="card-pres-bart" onclick="toggleBoolean('input_boolean.bart_thuis')">
                <div class="light-card-info">
                    <div class="light-icon">👤</div>
                    <div class="light-title">Bart</div>
                </div>
            </div>
            <div class="light-card light-off" id="card-pres-linda" onclick="toggleBoolean('input_boolean.linda_thuis')">
                <div class="light-card-info">
                    <div class="light-icon">👤</div>
                    <div class="light-title">Linda</div>
                </div>
            </div>
            <div class="light-card light-off" id="card-pres-gasten" onclick="toggleBoolean('input_boolean.gasten_thuis')">
                <div class="light-card-info">
                    <div class="light-icon">👥</div>
                    <div class="light-title">Gasten</div>
                </div>
            </div>
        </div>

      </div>
    </details>

    <!-- Anniversaries -->
    <details id="anniversaryBlock" class="auto-section" style="display:none; margin-bottom:20px;">
      <summary class="energy-subtitle">
        <svg width="12" height="12" viewBox="0 0 12 12" fill="none" style="margin-right: 8px;"><path d="M6 1v2M3 2l1 1.5M9 2l-1 1.5M1 5h10M2 5l1 6h6l1-6" stroke="currentColor" stroke-width="1.2" fill="none" stroke-linecap="round" stroke-linejoin="round"/></svg>
        Verjaardagen
      </summary>
      <div class="auto-section-content">
        <div id="anniversaryList"></div>
      </div>
    </details>



  </div>

  <!-- ══ RIGHT sidebar ══ -->
  <div class="right">

      <?php include 'sidebar.php'; ?>

  </div>
</main>

<script>
  // ════════════════════════════════════════════════
  //  ⚙️  CONFIGURATIE — pas alleen dit blok aan
  // ════════════════════════════════════════════════
  const REFRESH  = 3000; // milliseconden tussen elke refresh

  // Sensoren Gecombineerd: Eerst 3x Rij 1, Dan 4x Rij 2 (inclusief batterij)
  const SENSOR_CONFIG = [
    // Rij 1 (3 items)
    { name: 'Voordeur',   motion: 'binary_sensor.voordeur_motion',      motionSwitch: 'switch.voordeur_sensor_motion_sensor_enabled',   lightSwitch: 'switch.voordeur_sensor_light_sensor_enabled',   battery: 'sensor.voordeur_sensor_battery', row: 1 },
    { name: 'Inkomhal',   motion: 'binary_sensor.inkomhal_sensor_motion', motionSwitch: 'switch.inkomhal_sensor_motion_sensor_enabled',  lightSwitch: 'switch.inkomhal_sensor_light_sensor_enabled',   battery: 'sensor.inkomhal_sensor_battery', row: 1 },
    { name: 'Eetkamer',   motion: 'binary_sensor.eetkamer_sensor_motion', motionSwitch: 'switch.eetkamer_sensor_motion_sensor_enabled',  lightSwitch: 'switch.eetkamer_sensor_light_sensor_enabled',   battery: 'sensor.eetkamer_sensor_battery', row: 1 },
    // Rij 2 (4 items)
    { name: 'Trap',       motion: 'binary_sensor.trap_sensor_motion',            motionSwitch: 'switch.trap_sensor_motion_sensor_enabled',            lightSwitch: 'switch.trap_sensor_light_sensor_enabled',            battery: 'sensor.trap_sensor_battery', row: 2 },
    { name: '1e Verd.',   motion: 'binary_sensor.1e_verdieping_sensor_motion',   motionSwitch: 'switch.1e_verdieping_sensor_motion_sensor_enabled',   lightSwitch: 'switch.1e_verdieping_sensor_light_sensor_enabled',   battery: 'sensor.1e_verdieping_sensor_battery', row: 2 },
    { name: '2de Verd.',  motion: 'binary_sensor.2de_verdieping_sensor_motion',  motionSwitch: 'switch.2de_verdieping_sensor_motion_sensor_enabled',  lightSwitch: 'switch.2de_verdieping_sensor_light_sensor_enabled',  battery: 'sensor.2de_verdieping_sensor_battery', row: 2 },
    { name: 'Buiten',     motion: 'binary_sensor.achterdeur_motion',             motionSwitch: 'switch.buitenlicht_sensor_motion_sensor_enabled',     lightSwitch: 'switch.buitenlicht_sensor_light_sensor_enabled',     battery: 'sensor.buitenlicht_sensor_battery', row: 2 }
  ];

  // Overige entities
  const ENTITIES = {
    alarm:        'input_boolean.alarm_actief',
    alarmZone:    'input_text.actieve_zone',
    internet:     'binary_sensor.internet_verbinding',
    speedDown:    'sensor.speedtest_download',
    speedUp:      'sensor.speedtest_upload',
    speedPing:    'sensor.speedtest_ping',
    versieLocal:  'sensor.versie_lokaal',
    nasUpdate:    'update.homenas_update',
    // Roomba
    roombaStatus: 'sensor.roomba_status',
    roombaAccu:   'sensor.roomba_accu',
    roombaBakje:  'binary_sensor.roomba_afvalbak_aanwezig',
    roombaVol:    'binary_sensor.roomba_afvalbak_vol',
    // Voordeur
    deurOpen:     'binary_sensor.nuki_front_door_door_open',
    deurSlot:     'binary_sensor.nuki_front_door_locked',
    deurBat:      'sensor.nuki_front_door_battery',
    // Temperatuur
    tempLiving:   'sensor.woonkamer_temp',
    tempSlaap:    'sensor.slaapkamer_temp',
    tempBureau:   'sensor.bureau_temp',
    tempBuiten:   'sensor.thuis_outdoor_temperature',
    // Alarm notificaties switches
    alarmToggle:              'input_boolean.alarm',
    fakepresence:             'input_boolean.fakepresence',
    notifyBart:               'input_boolean.notify_bart',
    bewegingDetectieBeneden:  'input_boolean.beweging_detectie_beneden',
    bewegingDetectieBoven:    'input_boolean.beweging_detectie_boven',
    bewegingDetectieBuiten:   'input_boolean.beweging_detectie_buiten',
    aanwezig:                 'input_boolean.aanwezig',
    bartThuis:                'input_boolean.bart_thuis',
    lindaThuis:               'input_boolean.linda_thuis',
    gastenThuis:              'input_boolean.gasten_thuis'
  };

  // Personen
  const PERSON_CONFIG = [
    { name: 'Bart',  person: 'person.bartp',  tracker: 'device_tracker.bart_iphone', location: 'sensor.b63p15_geocoded_location' },
    { name: 'Linda', person: 'person.linda',  tracker: 'device_tracker.linda', location: 'sensor.iphone_linda_geocoded_location' },
  ];



  // Anniversary entities
  const ANNIVERSARY_ENTITIES = [];

  // ════════════════════════════════════════════════

  // ── Clock ──
  function tick() {
    document.getElementById('clock').textContent =
      new Date().toLocaleTimeString('nl-BE', { hour12: false });
  }
  tick();
  setInterval(tick, 1000);

  // ── Render helpers ──
  function battColor(p) {
    if (p > 60) return 'var(--ok)';
    if (p > 25) return 'var(--warn)';
    return 'var(--alert)';
  }

  // ── Build sensor cards (once) ──
  const grid = document.getElementById('sensorGrid');
  SENSOR_CONFIG.forEach((s, i) => {
    const card = document.createElement('div');
    card.id = `sensor-${i}`;
    card.className = 'sensor-card ok';
    card.setAttribute('data-row', s.row);
    card.innerHTML = `
      <div class="sensor-batt" id="sensor-batt-${i}">--%</div>
      <div class="sensor-name">${s.name}</div>
      <div class="sensor-status">—</div>
      <div class="sensor-toggles">
        <div class="toggle-chip motion-off"><div class="chip-dot"></div> Beweging</div>
        <div class="toggle-chip light-off"><div class="chip-dot"></div> Licht</div>
      </div>`;
    grid.appendChild(card);
  });

  // ── Update Sensors & Individual Batteries ──
  async function updateSensors(stateMap) {
    SENSOR_CONFIG.forEach((s, i) => {
      const card   = document.getElementById(`sensor-${i}`);
      const motion = stateVal(stateMap[s.motion]) === 'on';
      const pirOn  = stateVal(stateMap[s.motionSwitch]) === 'on';
      const luxOn  = stateVal(stateMap[s.lightSwitch])  === 'on';

      card.className = `sensor-card ${motion ? 'alert' : 'ok'}`;
      card.querySelector('.sensor-status').textContent = motion ? 'Beweging!' : 'OK';

      const chips = card.querySelectorAll('.toggle-chip');
      chips[0].className = `toggle-chip ${pirOn ? 'motion-on' : 'motion-off'}`;
      chips[1].className = `toggle-chip ${luxOn  ? 'light-on'  : 'light-off'}`;

      // Update in-card battery
      const battEl = document.getElementById(`sensor-batt-${i}`);
      if (s.battery && stateMap[s.battery]) {
        let bVal = parseInt(stateMap[s.battery].state);
        if (!isNaN(bVal)) {
          battEl.innerHTML = `<span>⚡</span> ${bVal}%`;
          battEl.style.color = battColor(bVal);
        } else {
          battEl.innerHTML = `<span>⚡</span> --%`;
          battEl.style.color = 'var(--dim)';
        }
      } else {
        battEl.style.display = 'none';
      }
    });
  }

  // ── Update alarm banner ──
  function updateAlarm(alarmState, zoneState) {
    const banner   = document.getElementById('alarmBanner');
    const alarmSub = document.getElementById('alarmSub');
    const on = stateVal(alarmState) === 'on';
    banner.className = `alarm-banner ${on ? 'active' : 'ok'}`;
    banner.querySelector('.alarm-icon').textContent  = on ? '⚠' : '✔';
    banner.querySelector('.alarm-title').textContent = on ? 'Alarm Melding' : 'Alles OK';
    const zone = stateVal(zoneState);
    alarmSub.textContent = on && zone !== 'Geen melding' ? zone : 'Geen actieve meldingen';
  }


  // ── Update versions (Lokaal & NAS next to Alarm) ──
  function updateVersions(states) {
    document.getElementById('ver-local').textContent  = stateVal(states[ENTITIES.versieLocal]);
    const nasOk = stateVal(states[ENTITIES.nasUpdate]) === 'off';
    const el = document.getElementById('ver-nas');
    el.textContent = nasOk ? 'OK' : 'UPDATE!';
    el.style.color = nasOk ? 'var(--ok)' : 'var(--alert)';

    const banner = document.getElementById('versionsBanner');
    banner.className = `versions-banner ${nasOk ? 'ok' : 'alert'}`;
  }






  // ── Update persons ──
  function updatePersons(stateMap) {
    const grid = document.getElementById('personsGrid');
    if (!grid) return;

    if (grid.children.length === 0) {
      PERSON_CONFIG.forEach((p, i) => {
        const card = document.createElement('div');
        card.id = `person-${i}`;
        card.className = 'person-card';
        card.innerHTML = `
          <div class="person-avatar" id="person-av-${i}" style="background-image: url('IMGS/${p.name.toLowerCase()}.png'); color: transparent;">${p.name.charAt(0)}</div>
          <div class="person-info">
            <div class="person-name">${p.name}</div>
            <div class="person-status" id="person-st-${i}">—</div>
            <div class="person-location" id="person-loc-${i}" style="display:none;"></div>
          </div>`;
        grid.appendChild(card);
      });
    }
    PERSON_CONFIG.forEach((p, i) => {
      const home = stateVal(stateMap[p.person]) === 'home';
      const tracker = stateMap[p.tracker];

      const card = document.getElementById(`person-${i}`);
      const av = document.getElementById(`person-av-${i}`);
      const locEl = document.getElementById(`person-loc-${i}`);

      if (!card || !av) return;
      card.className = `person-card ${home ? 'home' : 'away'}`;
      av.className = `person-avatar ${home ? 'home' : 'away'}`;
      document.getElementById(`person-st-${i}`).textContent = home ? '● THUIS' : '● WEG';

      if (p.location && stateMap[p.location]) {
          const locState = stateMap[p.location].state;
          if (locState && locState !== 'unavailable' && locState !== 'unknown') {
              locEl.textContent = locState;
              locEl.style.display = 'block';
          } else {
              locEl.style.display = 'none';
          }
      } else if (locEl) {
          locEl.style.display = 'none';
      }
    });
  }

  // ── Update anniversaries ──
  async function updateAnniversaries(stateMap) {
    const upcoming = [];
    ANNIVERSARY_ENTITIES.forEach(id => {
      const s = stateMap[id];
      if (!s) return;
      const days = parseInt(s.state, 10);
      if (isNaN(days) || days < 0 || days > 7) return;
      const name = (s.attributes?.friendly_name ?? id)
        .replace('anniversary_', '').replace(/_/g, ' ');
      upcoming.push({ days, name });
    });
    upcoming.sort((a, b) => a.days - b.days);

    const block = document.getElementById('anniversaryBlock');
    const list  = document.getElementById('anniversaryList');
    list.innerHTML = '';

    if (upcoming.length === 0) { block.style.display = 'none'; return; }
    block.style.display = 'block';

    upcoming.forEach(({ days, name }) => {
      const emoji = days === 0 ? '🎉' : days === 1 ? '🎂' : days === 2 ? '🎁' : '📅';
      const when  = days === 0 ? 'Vandaag!' : days === 1 ? 'Morgen' : days === 2 ? 'Overmorgen' : `Over ${days} dagen`;
      const item = document.createElement('div');
      item.className = 'anniversary-item';
      item.innerHTML = `
        <span class="anniversary-emoji">${emoji}</span>
        <span class="anniversary-name">${name}</span>
        <span class="anniversary-when">${when}</span>`;
      list.appendChild(item);
    });
  }

  async function toggleBoolean(entityId) {
      try {
          await haPost('input_boolean', 'toggle', entityId);
          setTimeout(refresh, 500);
      } catch (err) {
          console.error('Failed to toggle boolean', err);
      }
  }

  // ── Main refresh ──
  async function refresh() {
    const allIds = [
      ...SENSOR_CONFIG.flatMap(s => [s.motion, s.motionSwitch, s.lightSwitch, s.battery].filter(Boolean)),
      ...Object.values(ENTITIES),
      ...PERSON_CONFIG.flatMap(p => [p.person, p.tracker]),
      ...PERSON_CONFIG.map(p => p.location).filter(Boolean),
      ...ANNIVERSARY_ENTITIES,
    ];

    const results = await haGetAll([...new Set(allIds)]);
    const stateMap = {};
    [...new Set(allIds)].forEach((id, i) => stateMap[id] = results[i]);

    updateSensors(stateMap);
    updateAlarm(stateMap[ENTITIES.alarm], stateMap[ENTITIES.alarmZone]);

    updateVersions(stateMap);



    updatePersons(stateMap);

    // Update new Alarm notification toggles (checkboxes)
    const togglesToSync = [
        { id: 'toggleAlarmHeader', entity: ENTITIES.alarmToggle },
        { id: 'toggleFakePresence', entity: ENTITIES.fakepresence },
        { id: 'toggleNotifyBart', entity: ENTITIES.notifyBart }
    ];
    togglesToSync.forEach(t => {
        const el = document.getElementById(t.id);
        const stateObj = stateMap[t.entity];
        if (el && stateObj) {
            el.checked = (stateObj.state === 'on');
        }
    });

    // Update the Alarm Notificaties Header styling based on the toggle state
    const alarmToggleState = stateMap[ENTITIES.alarmToggle];
    const alarmSummary = document.getElementById('alarmNotificatiesSummary');
    if (alarmToggleState && alarmSummary) {
        if (alarmToggleState.state === 'on') {
            alarmSummary.classList.add('alarm-header-active');
        } else {
            alarmSummary.classList.remove('alarm-header-active');
        }
    }

    // Update Light Cards
    const lightCardsToSync = [
        { id: 'card-zone-beneden', entity: ENTITIES.bewegingDetectieBeneden },
        { id: 'card-zone-boven', entity: ENTITIES.bewegingDetectieBoven },
        { id: 'card-zone-buiten', entity: ENTITIES.bewegingDetectieBuiten },
        { id: 'card-pres-all', entity: ENTITIES.aanwezig },
        { id: 'card-pres-bart', entity: ENTITIES.bartThuis },
        { id: 'card-pres-linda', entity: ENTITIES.lindaThuis },
        { id: 'card-pres-gasten', entity: ENTITIES.gastenThuis }
    ];
    lightCardsToSync.forEach(c => {
        const el = document.getElementById(c.id);
        const stateObj = stateMap[c.entity];
        if (el && stateObj) {
            el.className = "light-card " + (stateObj.state === 'on' ? "light-on light-ok" : "light-off");
        }
    });

    await updateAnniversaries(stateMap);

    document.getElementById('lastRefresh').textContent =
      'Bijgewerkt: ' + new Date().toLocaleTimeString('nl-BE');
  }

  refresh();
  setInterval(() => { if (!window.isRefreshPaused) refresh(); }, REFRESH);

  document.addEventListener('DOMContentLoaded', () => {
      // LocalStorage for auto-section details elements
      document.querySelectorAll('.auto-section').forEach(details => {
          const id = details.id;
          // Skip anniversary block for default storage, it is handled by the script's display block/none
          if (!id || id === 'anniversaryBlock') return;
          const lsKey = 'auto_section_mon_' + id;
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
