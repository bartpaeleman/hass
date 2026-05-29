<?php
// ha_core.js.php - Dynamische JS die variabelen en functies levert

// Beveiliging: weiger directe toegang als de referrer niet van de eigen host komt
$host = $_SERVER['HTTP_HOST'] ?? '';
$referer = $_SERVER['HTTP_REFERER'] ?? '';
if (empty($referer) || strpos($referer, $host) === false) {
    header("HTTP/1.1 403 Forbidden");
    exit("/* 403 Forbidden - Direct access not allowed */");
}

header("Content-type: application/javascript; charset=utf-8");
require_once 'config.php';
require_once 'CLASSES/Comfort.php';
?>
// Globale Authenticatie Variabelen
const HA_URL   = "<?php echo HA_URL; ?>";
const HA_TOKEN = "<?php echo HA_TOKEN; ?>";
const COMFORT_BOUNDARIES = <?php echo json_encode(Comfort::getBoundaries()); ?>;

function getComfortColor(currentTemp, roomName) {
  if (currentTemp == null || isNaN(currentTemp)) return 'var(--text)';
  const bounds = COMFORT_BOUNDARIES[roomName] || {min: 19.0, max: 22.5};
  const minComfort = bounds.min;
  const maxComfort = bounds.max;
  if (currentTemp < minComfort - 2.0) return '#00b4d8';
  if (currentTemp > maxComfort + 2.0) return 'var(--alert)';
  if (currentTemp > maxComfort) return '#ff8c42';
  return 'var(--ok)';
}


// ── HA API Helpers (Centraal voor elk dashboard) ──
async function haGet(entityId) {
  const r = await fetch(`${HA_URL}/api/states/${entityId}`, {
    headers: { 
      Authorization: `Bearer ${HA_TOKEN}`, 
      'Content-Type': 'application/json' 
    }
  });
  if (!r.ok) throw new Error(`HA ${entityId}: ${r.status}`);
  return r.json();
}

async function haGetAll(ids) {
  return Promise.all(ids.map(id => haGet(id).catch(() => ({ state: 'unavailable', attributes: {} }))));
}

function stateVal(s) { return s?.state ?? 'unavailable'; }

// ── Refresh Pause/Resume Logic ──
window.isRefreshPaused = false;

document.addEventListener('DOMContentLoaded', () => {
  const liveBadges = document.querySelectorAll('.live-badge');
  liveBadges.forEach(badge => {
    // Replace text node content without destroying the dot
    const textNode = Array.from(badge.childNodes).find(n => n.nodeType === Node.TEXT_NODE && n.textContent.trim() !== '');

    badge.addEventListener('click', () => {
      window.isRefreshPaused = !window.isRefreshPaused;
      if (window.isRefreshPaused) {
        badge.classList.add('paused');
        if (textNode) textNode.textContent = ' PAUZE';
      } else {
        badge.classList.remove('paused');
        if (textNode) textNode.textContent = ' LIVE';
      }
    });
  });

  // Mobile sidebar toggle injection
  const headerRight = document.querySelector('.header-right');
  if (headerRight) {
    const toggleBtn = document.createElement('div');
    toggleBtn.className = 'sidebar-toggle';
    toggleBtn.innerHTML = '☰ MENU';
    headerRight.appendChild(toggleBtn);

    toggleBtn.addEventListener('click', () => {
      const rightSidebar = document.querySelector('.right');
      if (rightSidebar) rightSidebar.classList.toggle('open');
    });
  }
});