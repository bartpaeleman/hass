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
const USER_ROLE_LEVEL = <?php echo isset($_SESSION['role_level']) ? $_SESSION['role_level'] : 10; ?>;
// Use PAGE_MIN_ACTION_LEVEL if defined in the specific dashboard, otherwise default to 50
const MIN_ACTION_LEVEL = typeof PAGE_MIN_ACTION_LEVEL !== 'undefined' ? PAGE_MIN_ACTION_LEVEL : 50;

function getComfortColor(currentTemp, roomName) {
  if (currentTemp == null || isNaN(currentTemp)) return 'var(--text)';
  const bounds = COMFORT_BOUNDARIES[roomName] || {min: 19.0, max: 22.5};
  const minComfort = bounds.min;
  const maxComfort = bounds.max;
  if (currentTemp < minComfort - 2.0) return 'var(--accent)';
  if (currentTemp > maxComfort + 2.0) return 'var(--alert)';
  if (currentTemp > maxComfort) return 'var(--heat)';
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

async function haPost(domain, service, entityId = "", payload = {}) {
  if (USER_ROLE_LEVEL < MIN_ACTION_LEVEL) {
    console.warn(`Actie geweigerd: Je hebt niet de juiste rechten (minimaal level ${MIN_ACTION_LEVEL}) om acties uit te voeren.`);
    return;
  }
  const url = `${HA_URL}/api/services/${domain}/${service}`;
  const body = { ...payload };
  if (entityId) body.entity_id = entityId;
  const r = await fetch(url, {
    method: "POST",
    headers: {
      Authorization: `Bearer ${HA_TOKEN}`,
      "Content-Type": "application/json"
    },
    body: JSON.stringify(body)
  });
  if (!r.ok) throw new Error(`HA POST ${domain}/${service}: ${r.status}`);
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

  // Theme Toggle logic
  const logoIcon = document.querySelector('.logo-icon');
  if (logoIcon) {
    logoIcon.style.cursor = 'pointer'; // Make it look clickable
    logoIcon.addEventListener('click', () => {
      const currentTheme = document.documentElement.getAttribute('data-theme') || 'dark';
      const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
      document.documentElement.setAttribute('data-theme', newTheme);
      localStorage.setItem('theme', newTheme);
    });
  }

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

// ── Global Click Interceptor for RBAC ──
// Intercepts clicks on actionable items in the left content area if the user lacks permissions.
document.addEventListener('click', (e) => {
  if (USER_ROLE_LEVEL >= MIN_ACTION_LEVEL) return;

  // We only block clicks within the main content area (so header/sidebar remain functional).
  const mainContent = e.target.closest('main .left');
  if (!mainContent) return;

  // Do not block <details> summary clicks (collapsible sections).
  if (e.target.closest('summary')) return;

  // Check if the clicked element looks like a button or actionable item
  const isActionable = e.target.closest('button') || e.target.closest('.filter-btn') || e.target.closest('[style*="cursor: pointer"]') || e.target.closest('[onclick]');

  if (isActionable) {
    console.warn(`Klik genegeerd: Je hebt level ${USER_ROLE_LEVEL}, maar level ${MIN_ACTION_LEVEL} is vereist om deze knop te gebruiken.`);
    e.stopPropagation();
    e.preventDefault();
  }
}, true); // Use capture phase to intercept before component listeners trigger