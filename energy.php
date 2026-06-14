<?php
require_once 'config.php';
$configFile = __DIR__ . '/JSON/config_data.json';
$configData = file_exists($configFile) ? json_decode(file_get_contents($configFile), true) : [];
$pageTitle = $configData['pages']['energy.php']['name'] ?? 'ENERGIE';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$currentRole = $_SESSION['role_level'] ?? 10;
$allowedRoles = $configData['pages']['energy.php']['roles'] ?? [99, 50, 10, 0];
if ('energy.php' !== 'index.php' && !in_array($currentRole, $allowedRoles) && $currentRole < 99) {
    header("HTTP/1.1 403 Forbidden");
    exit("Toegang geweigerd. Onvoldoende rechten voor deze pagina.");
}
require_once __DIR__ . '/CLASSES/EnergyClass.php';

// Check if this is an AJAX request for processed energy data
if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
    header('Content-Type: application/json');

    // In a real scenario, you might fetch raw data from HA here if needed on the backend,
    // but typically `energy.php` already loads it via JS. If you want the backend to fetch it,
    // you'd use a cURL call to HA API here.
    // For this example, we assume raw JSON data could be POSTed or fetched.
    $rawSensorData = json_decode(file_get_contents('php://input'), true) ?: [];

    $manager = new EnergyManager();
    echo json_encode($manager->processData($rawSensorData));
    exit;
}
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
<script>
  (function() {
  const PAGE_MIN_ACTION_LEVEL = 50;    
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
      <span>VERBRUIK · TARIEVEN</span>
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
      <div class="energy-subtitle">
          <span>⚡</span> Realtime Energie Flow
      </div>
      <div class="energy-grid">
          <div class="energy-card col-4" id="card-p1-net">
              <div class="energy-card-header">
                  <div class="energy-icon blue" id="icon-p1-net">⚡</div>
                  <div class="energy-title">Verbruik</div>
              </div>
              <div class="energy-value" id="val-p1-net">—</div>
          </div>
          <div class="energy-card col-4">
              <div class="energy-card-header">
                  <div class="energy-icon yellow">☀️</div>
                  <div class="energy-title">Zon productie</div>
              </div>
              <div class="energy-value" id="val-zon-prod">—</div>
          </div>
          <div class="energy-card col-4" id="card-bruto-verbruik">
              <div class="energy-card-header">
                  <div class="energy-icon red" id="icon-bruto-verbruik">⚡</div>
                  <div class="energy-title">Bruto verbruik</div>
              </div>
              <div class="energy-value" id="val-bruto-verbruik">—</div>
          </div>
      </div>

      <div class="energy-subtitle">
          <span>🌍</span> Zelfvoorziening
      </div>
      <div class="energy-grid">
          <div class="energy-card col-3">
              <div class="energy-card-header">
                  <div class="energy-icon blue">ℹ️</div>
                  <div class="energy-title">Status</div>
              </div>
              <div class="energy-value" id="val-energie-status" style="font-size:16px;">—</div>
          </div>
          <div class="energy-card col-3">
              <div class="energy-card-header">
                  <div class="energy-icon yellow">☀️</div>
                  <div class="energy-title">Zelfvoorz.</div>
              </div>
              <div class="energy-value" id="val-zelfvoorziening">—</div>
          </div>
          <div class="energy-card col-3">
              <div class="energy-card-header">
                  <div class="energy-icon blue">🔌</div>
                  <div class="energy-title">Net Afh.</div>
              </div>
              <div class="energy-value" id="val-net-afh">—</div>
          </div>
          <div class="energy-card col-3">
              <div class="energy-card-header">
                  <div class="energy-icon green">🌱</div>
                  <div class="energy-title">Autonomie</div>
              </div>
              <div class="energy-value" id="val-autonomie">—</div>
          </div>
      </div>

      <div class="energy-grid" style="margin-bottom: 8px;">
          <div class="col-6">
              <div class="energy-subtitle" style="margin-bottom: 0; border-bottom: none;">
                  <span>🔋</span> Batterij Control
              </div>
          </div>
          <div class="col-6">
              <div class="energy-subtitle" style="margin-bottom: 0; border-bottom: none;">
                  <span>🔌</span> Net Flow
              </div>
          </div>
      </div>

      <div class="energy-grid">
          <div class="energy-card stacked-card col-3" id="card-soc">
              <div class="energy-card-header">
                  <div class="energy-icon green" id="icon-soc">⚡</div>
                  <div class="energy-title">Charge / Status</div>
              </div>
              <div class="energy-value">
                  <span id="val-soc">—</span>
                  <span id="val-batt-status" style="margin-left:8px; font-size:16px;">—</span>
              </div>
          </div>
          <div class="energy-card stacked-card col-3">
              <div class="energy-card-header">
                  <div class="energy-icon green">🔋</div>
                  <div class="energy-title">Vermogen</div>
              </div>
              <div class="energy-value" id="val-batt-vermogen">—</div>
          </div>

          <div class="energy-card stacked-card col-3" id="card-net-import">
              <div class="energy-card-header">
                  <div class="energy-icon red" id="icon-net-import">⬇️</div>
                  <div class="energy-title">Import</div>
              </div>
              <div class="energy-value" id="val-net-import">—</div>
          </div>
          <div class="energy-card stacked-card col-3">
              <div class="energy-card-header">
                  <div class="energy-icon blue">⬆️</div>
                  <div class="energy-title">Injectie</div>
              </div>
              <div class="energy-value" id="val-net-injectie">—</div>
          </div>
      </div>
  </div>

  <div class="right">
      <?php include 'sidebar.php'; ?>
  </div>
</main>

<script>
  const REFRESH  = 3000;

  function tick() {
    document.getElementById('clock').textContent =
      new Date().toLocaleTimeString('nl-BE', { hour12: false });
  }
  tick();
  setInterval(tick, 1000);

  const ENERGY_ENTITIES = {
    p1Net: 'sensor.huisverbruik_totaal',
    zonProd: 'sensor.zonneenergie_productie_nu',
    brutoVerbruik: 'sensor.actueel_bruto_elektriciteitsverbruik',
    aircoOverschot: 'sensor.airco_solar_eligible',
    huisverbruik: 'sensor.huisverbruik_totaal',
    energieStatus: 'sensor.energie_status',
    zelfvoorziening: 'sensor.zelfvoorzieningsgraad',
    netAfh: 'sensor.net_afhankelijkheid',
    autonomie: 'sensor.energie_autonomie_index',
    soc: 'sensor.adj0b1302u_state_of_charge',
    battVermogen: 'sensor.batterij_vermogen',
    battStatus: 'sensor.batterij_status',
    netImport: 'sensor.electriciteit_netverbruik_nu',
    netInjectie: 'sensor.electriciteit_injectie_nu'
  };


  async function refresh() {
    const allIds = Object.values(ENERGY_ENTITIES);

    if (allIds.length > 0) {
      const results = await haGetAll(allIds);
      const stateMap = {};
      allIds.forEach((id, i) => stateMap[id] = results[i]);

      const formatVal = (stateObj) => {
        if (!stateObj || stateObj.state === 'unavailable') return '—';
        let val = stateObj.state;
        if (!isNaN(parseFloat(val))) val = parseFloat(val).toFixed(2);
        return val + (stateObj.attributes?.unit_of_measurement ? ` <span class="energy-unit">${stateObj.attributes.unit_of_measurement}</span>` : '');
      };

      const p1NetObj = stateMap[ENERGY_ENTITIES.p1Net];
      const cardP1 = document.getElementById('card-p1-net');
      const iconP1 = document.getElementById('icon-p1-net');
      if (p1NetObj && p1NetObj.state !== 'unavailable') {
          let valP1 = parseFloat(p1NetObj.state);
          let colorClass = 'bg-blue';
          let iconClass = 'blue';
          if (valP1 < 0) {
              colorClass = 'bg-green';
              iconClass = 'green';
          } else if (valP1 > 1000) {
              colorClass = 'bg-red';
              iconClass = 'red';
          }
          document.getElementById('val-p1-net').innerHTML = valP1.toFixed(0) + ` <span class="energy-unit">${p1NetObj.attributes?.unit_of_measurement || 'W'}</span>`;
          if (cardP1) cardP1.className = `energy-card col-4 ${colorClass}`;
          if (iconP1) iconP1.className = `energy-icon ${iconClass}`;
      } else {
          document.getElementById('val-p1-net').innerHTML = '—';
      }

      document.getElementById('val-zon-prod').innerHTML = formatVal(stateMap[ENERGY_ENTITIES.zonProd]);

      const brutoVerbruikObj = stateMap[ENERGY_ENTITIES.brutoVerbruik];
      const cardBruto = document.getElementById('card-bruto-verbruik');
      const iconBruto = document.getElementById('icon-bruto-verbruik');
      if (brutoVerbruikObj && brutoVerbruikObj.state !== 'unavailable') {
          let valBruto = parseFloat(brutoVerbruikObj.state);
          let colorClass = 'bg-red';
          let iconClass = 'red';
          if (valBruto < 500) {
              colorClass = 'bg-green';
              iconClass = 'green';
          } else if (valBruto <= 1200) {
              colorClass = 'bg-yellow';
              iconClass = 'yellow';
          }
          document.getElementById('val-bruto-verbruik').innerHTML = valBruto.toFixed(0) + ` <span class="energy-unit">${brutoVerbruikObj.attributes?.unit_of_measurement || 'W'}</span>`;
          if (cardBruto) cardBruto.className = `energy-card col-4 ${colorClass}`;
          if (iconBruto) iconBruto.className = `energy-icon ${iconClass}`;
      } else {
          document.getElementById('val-bruto-verbruik').innerHTML = '—';
      }



      const socObj = stateMap[ENERGY_ENTITIES.soc];
      const cardSoc = document.getElementById('card-soc');
      const iconSoc = document.getElementById('icon-soc');
      if (socObj && socObj.state !== 'unavailable') {
        let valSoc = parseFloat(socObj.state);
        let colorClass = 'bg-green';
        let iconClass = 'green';
        if (valSoc <= 10) {
            colorClass = 'bg-red';
            iconClass = 'red';
        } else if (valSoc <= 50) {
            colorClass = 'bg-yellow';
            iconClass = 'yellow';
        }
        document.getElementById('val-soc').innerHTML = valSoc.toFixed(0) + ` <span class="energy-unit">%</span>`;
        if (cardSoc) cardSoc.className = `energy-card stacked-card col-3 ${colorClass}`;
        if (iconSoc) iconSoc.className = `energy-icon ${iconClass}`;
      } else {
        document.getElementById('val-soc').innerHTML = '—';
      }

      document.getElementById('val-batt-vermogen').innerHTML = formatVal(stateMap[ENERGY_ENTITIES.battVermogen]);
      const battStatusObj = stateMap[ENERGY_ENTITIES.battStatus];
      const battStatusEl = document.getElementById('val-batt-status');
      if (battStatusObj && battStatusObj.state !== 'unavailable') {
         let statusText = battStatusObj.state.toUpperCase();
         battStatusEl.textContent = statusText;
         if (statusText === 'LADEN') {
             battStatusEl.style.color = 'var(--warn)';
         } else if (statusText === 'ONTLADEN' || statusText === 'ONTLAGEN') {
             battStatusEl.style.color = 'var(--ok)';
         } else if (statusText === 'IDLE' || statusText === 'IDEL') {
             battStatusEl.style.color = '#fff';
         } else {
             battStatusEl.style.color = '';
         }
      } else {
         battStatusEl.textContent = '—';
         battStatusEl.style.color = '';
      }

      const importObj = stateMap[ENERGY_ENTITIES.netImport];
      const cardImport = document.getElementById('card-net-import');
      const iconImport = document.getElementById('icon-net-import');

      if (importObj && importObj.state !== 'unavailable') {
        let valImport = parseFloat(importObj.state);
        let colorClass = 'bg-red';
        let iconClass = 'red';

        if (valImport <= 0) {
            valImport = 0;
            colorClass = 'bg-green';
            iconClass = 'green';
        } else if (valImport <= 1200) {
            colorClass = 'bg-yellow';
            iconClass = 'yellow';
        }

        document.getElementById('val-net-import').innerHTML = valImport.toFixed(0) + ` <span class="energy-unit">${importObj.attributes?.unit_of_measurement || 'W'}</span>`;
        if (cardImport) cardImport.className = `energy-card stacked-card col-3 ${colorClass}`;
        if (iconImport) iconImport.className = `energy-icon ${iconClass}`;
      } else {
        document.getElementById('val-net-import').innerHTML = '—';
      }

      document.getElementById('val-net-injectie').innerHTML = formatVal(stateMap[ENERGY_ENTITIES.netInjectie]);
      document.getElementById('val-energie-status').innerHTML = formatVal(stateMap[ENERGY_ENTITIES.energieStatus]);
      document.getElementById('val-zelfvoorziening').innerHTML = formatVal(stateMap[ENERGY_ENTITIES.zelfvoorziening]);

      const netAfhObj = stateMap[ENERGY_ENTITIES.netAfh];
      if (netAfhObj && netAfhObj.state !== 'unavailable') {
         document.getElementById('val-net-afh').innerHTML = parseFloat(netAfhObj.state).toFixed(0) + ' <span class="energy-unit">%</span>';
      } else {
         document.getElementById('val-net-afh').innerHTML = '—';
      }

      const autObj = stateMap[ENERGY_ENTITIES.autonomie];
      if (autObj && autObj.state !== 'unavailable') {
         document.getElementById('val-autonomie').innerHTML = parseFloat(autObj.state).toFixed(0) + ' <span class="energy-unit">%</span>';
      } else {
         document.getElementById('val-autonomie').innerHTML = '—';
      }
    }

    document.getElementById('lastRefresh').textContent =
      'Bijgewerkt: ' + new Date().toLocaleTimeString('nl-BE');
  }

  refresh();
  setInterval(() => { if (!window.isRefreshPaused) refresh(); }, REFRESH);
</script>
</body>
</html>
