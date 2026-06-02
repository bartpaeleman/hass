<?php
require_once 'config.php';

// Data processing logic
$eventsFile = __DIR__ . '/JSON/events.json';
$events = [];
if (file_exists($eventsFile)) {
    $content = file_get_contents($eventsFile);
    // Strip BOM if present
    if (substr($content, 0, 3) === "\xef\xbb\xbf") {
        $content = substr($content, 3);
    }
    $eventsData = json_decode($content, true);
    if (is_array($eventsData)) {
        $events = $eventsData;
    }
}

$today = new DateTime('today');
$processedEvents = [];

// Helper functies voor flexibele datums
function getEasterDate($year) {
    // PHP heeft ingebouwde functies voor Pasen: easter_days of easter_date.
    // easter_days geeft het aantal dagen na 21 maart.
    $days = easter_days($year);
    $date = new DateTime("$year-03-21");
    $date->modify("+$days days");
    return $date;
}

function getSpecificWeekday($year, $occurrence, $dayOfWeek, $month) {
    // dayOfWeek in JSON: 0 = Zondag, 1 = Maandag...
    // PHP DateTime/strtotime format: "first sunday of may 2026", "last sunday of october 2026"
    $dayNames = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
    $monthNames = ['', 'january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'december'];

    $dName = $dayNames[$dayOfWeek];
    $mName = $monthNames[$month];

    if ($occurrence === 'last') {
        $str = "last $dName of $mName $year";
    } else {
        $occNames = ['', 'first', 'second', 'third', 'fourth', 'fifth'];
        $oName = $occNames[$occurrence] ?? 'first';
        $str = "$oName $dName of $mName $year";
    }

    return new DateTime($str);
}

function getThanksgivingDate($year) {
    // 4e donderdag in november
    return getSpecificWeekday($year, 4, 4, 11);
}

function calculateNextDate($event, $baseYear) {
    if (!isset($event['type'])) return null;

    if ($event['type'] === 'vast' && isset($event['date'])) {
        $parts = explode('-', $event['date']);
        if (count($parts) === 3) {
            $month = $parts[1];
            $day = $parts[2];
        } elseif (count($parts) === 2) {
            $month = $parts[0];
            $day = $parts[1];
        } else {
            return null;
        }
        return new DateTime("$baseYear-$month-$day");
    }

    if ($event['type'] === 'flexibel' && isset($event['formula'])) {
        $tokens = explode(' ', $event['formula']);
        $cmd = $tokens[0] ?? '';

        if ($cmd === 'easter') {
            $offset = (int)($tokens[1] ?? 0);
            $date = getEasterDate($baseYear);
            $date->modify("$offset days");
            return $date;
        }
        if ($cmd === 'weekday') {
            $occ = $tokens[1];
            $dayOfWeek = (int)($tokens[2] ?? 0);
            $month = (int)($tokens[3] ?? 1);
            return getSpecificWeekday($baseYear, $occ, $dayOfWeek, $month);
        }
        if ($cmd === 'thanksgiving') {
            $offset = (int)($tokens[1] ?? 0);
            $date = getThanksgivingDate($baseYear);
            $date->modify("$offset days");
            return $date;
        }
    }
    return null;
}

$months = [
    1 => 'jan', 2 => 'feb', 3 => 'mrt', 4 => 'apr',
    5 => 'mei', 6 => 'jun', 7 => 'jul', 8 => 'aug',
    9 => 'sep', 10 => 'okt', 11 => 'nov', 12 => 'dec'
];

foreach ($events as $event) {
    if (!isset($event['name'])) continue;

    $currentYear = (int)$today->format('Y');

    // First, try to calculate for this year
    $nextDate = calculateNextDate($event, $currentYear);
    if (!$nextDate) continue; // Skip invalid formats

    $nextDate->setTime(0,0,0);

    // Check if the event has already passed this year
    $hasPassedThisYear = false;
    if ($nextDate < $today) {
        $hasPassedThisYear = true;
        // Compute for next year to get the correct next occurrence date and days remaining
        $nextDate = calculateNextDate($event, $currentYear + 1);
        $nextDate->setTime(0,0,0);
    }

    $interval = $today->diff($nextDate);
    $daysRemaining = (int)$interval->format('%a');

    // Calculate years if applicable (for birthdays and weddings)
    $currentYears = null;
    $nextYears = null;
    $category = strtolower($event['category'] ?? '');
    $isBirthdayOrWedding = in_array($category, ['verjaardag', 'huwelijk']);

    // Only calculate years if the event is 'vast' and has a YYYY-MM-DD format (3 parts)
    $originalYear = null;
    if ($event['type'] === 'vast' && isset($event['date'])) {
        $parts = explode('-', $event['date']);
        if (count($parts) === 3) {
            $originalYear = (int)$parts[0];
        }
    }

    if ($originalYear !== null && $isBirthdayOrWedding) {
        $currentYears = $currentYear - $originalYear;
        // If the date hasn't happened yet this year, they haven't reached the "current" age for this year yet
        $thisYearDate = calculateNextDate($event, $currentYear);
        $thisYearDate->setTime(0,0,0);
        if ($thisYearDate > $today) {
            $currentYears--;
        }
        $nextYears = ((int)$nextDate->format('Y')) - $originalYear;
    }

    $info = isset($event['info']) ? $event['info'] : '';

    $highlightMessage = '';
    if ($category === 'verjaardag' && $nextYears !== null) {
        $highlightMessage = "wordt $nextYears jaar";
    } elseif ($category === 'huwelijk' && $nextYears !== null) {
        $highlightMessage = "$nextYears jaar getrouwd";
    } elseif ($category === 'feestdag') {
        $highlightMessage = "Feestdag";
    }

    $filterClass = 'filter-andere';
    if (($category === 'verjaardag' || $category === 'huwelijk') && stripos($info, 'gezin') !== false) {
        $filterClass = 'filter-gezin';
    } elseif (($category === 'verjaardag' || $category === 'huwelijk') && stripos($info, 'familie') !== false) {
        $filterClass = 'filter-familie';
    } elseif ($category === 'feestdag') {
        $filterClass = 'filter-feestdagen';
    } elseif ($category === 'sport') {
        $filterClass = 'filter-sport';
    }

    $formattedDate = $nextDate->format('j') . ' ' . $months[(int)$nextDate->format('n')];
    if ($originalYear !== null && $isBirthdayOrWedding) {
        $formattedDate .= ' ' . $originalYear;
    }

    $processedEvents[] = [
        'original' => $event,
        'category' => $category,
        'nextDate' => $nextDate,
        'daysRemaining' => $daysRemaining,
        'formattedDate' => $formattedDate,
        'currentYears' => $currentYears,
        'nextYears' => $nextYears,
        'highlightMessage' => $highlightMessage,
        'hasPassedThisYear' => $hasPassedThisYear,
        'filterClass' => $filterClass,
        'info' => $info
    ];
}

// Sort by days remaining (global)
usort($processedEvents, function($a, $b) {
    return $a['daysRemaining'] <=> $b['daysRemaining'];
});

$upcomingEventsRaw = array_filter($processedEvents, function($e) {
    return $e['daysRemaining'] <= 5;
});

// Group upcoming events by daysRemaining -> category
$upcomingGrouped = [];
foreach ($upcomingEventsRaw as $e) {
    $d = $e['daysRemaining'];
    $cat = $e['category'];
    if (!isset($upcomingGrouped[$d])) {
        $upcomingGrouped[$d] = [];
    }
    if (!isset($upcomingGrouped[$d][$cat])) {
        $upcomingGrouped[$d][$cat] = [];
    }
    $upcomingGrouped[$d][$cat][] = $e;
}

// Group by next occurrence month (1-12)
$eventsByMonth = [];
for ($m = 1; $m <= 12; $m++) {
    $eventsByMonth[$m] = [];
}
foreach ($processedEvents as $e) {
    $m = (int)$e['nextDate']->format('n');
    $eventsByMonth[$m][] = $e;
}

// Determine active month for highlighting
$currentMonth = (int)$today->format('n');

?>
<!DOCTYPE html>
<html lang="nl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Speciale Dagen</title>
<link href="https://fonts.googleapis.com/css2?family=Share+Tech+Mono&family=Barlow+Condensed:wght@300;400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="CSS/common.css">
<link rel="stylesheet" href="CSS/speciale_dagen.css">
<script>
  const PAGE_MIN_ACTION_LEVEL = 0;
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
      <h1>Speciale Dagen</h1>
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

    <!-- Aankomende Speciale Dagen (Max 5 dagen) -->
    <?php if (count($upcomingGrouped) > 0): ?>
    <div class="section-label" style="margin-bottom:12px;">
      <svg width="12" height="12" viewBox="0 0 12 12" fill="none" style="vertical-align: middle; margin-right:4px;"><path d="M6 1v6.5M6 10a2 2 0 100-4 2 2 0 000 4z" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/><path d="M8 3h1M8 5h1" stroke="currentColor" stroke-width="1" stroke-linecap="round"/></svg>
      Komende 5 dagen
    </div>
    <div class="upcoming-grid">
      <?php
      ksort($upcomingGrouped);
      foreach ($upcomingGrouped as $daysRemaining => $categories):
          $dayText = '';
          $borderColor = 'var(--accent)';
          if ($daysRemaining == 0) {
              $dayText = 'Vandaag!';
              $borderColor = 'var(--ok)';
          } elseif ($daysRemaining == 1) {
              $dayText = 'Morgen';
              $borderColor = 'var(--warn)';
          } elseif ($daysRemaining == 2) {
              $dayText = 'Overmorgen';
              $borderColor = 'var(--heat)';
          } else {
              $dayText = 'Binnen ' . $daysRemaining . ' dagen';
          }

          foreach ($categories as $cat => $eventsInCat):
              $icon = '📅';
              if ($cat === 'verjaardag') $icon = '🎂';
              elseif ($cat === 'huwelijk') $icon = '💍';
              elseif ($cat === 'feestdag') $icon = '🎉';
              elseif ($cat === 'sport') $icon = '⚽';

              // Date is the same for all events in this group
              $formattedDate = $eventsInCat[0]['formattedDate'];
      ?>
      <div class="upcoming-card" style="border-color: <?php echo $borderColor; ?>">
        <div class="uc-icon"><?php echo $icon; ?></div>
        <div class="uc-details">
          <div class="uc-date"><?php echo $dayText; ?> (<?php echo $formattedDate; ?>)</div>
          <div class="uc-items-list">
            <?php foreach ($eventsInCat as $e): ?>
              <div class="uc-item" data-filter="<?php echo $e['filterClass']; ?>">
                <div class="uc-name-wrap">
                  <span class="cat-label cat-<?php echo htmlspecialchars($e['category']); ?>"><?php echo strtoupper(htmlspecialchars($e['category'])); ?>:</span>
                  <span class="uc-name"><?php echo htmlspecialchars($e['original']['name']); ?></span>
                  <?php if (!empty($e['info'])): ?>
                    <span style="color:var(--text-muted); font-size: 0.9em; margin-left: 5px;">- <?php echo htmlspecialchars($e['info']); ?></span>
                  <?php endif; ?>
                </div>
                <?php if ($e['highlightMessage']): ?>
                  <span class="msg-highlight"><?php echo $e['highlightMessage']; ?></span>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
      <?php endforeach; endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Alle Speciale Dagen per Maand -->
    <div class="section-label" style="margin-top:24px; margin-bottom:12px;">
      <svg width="12" height="12" viewBox="0 0 12 12" fill="none" style="vertical-align: middle; margin-right:4px;"><path d="M2 3h8M2 6h8M2 9h8" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/></svg>
      Events
    </div>

    <div class="filters-container" style="display: flex; gap: 8px; margin-bottom: 16px; flex-wrap: wrap;">
      <?php if (isset($_SESSION['role_level']) && $_SESSION['role_level'] >= 50): ?>
      <a href="ADMIN/events_admin.php?json-events=events.json" class="filter-btn" style="text-decoration: none; border-color: var(--warn); color: var(--warn);">⚙️ <span class="filter-text">BEHEER</span></a>
      <?php endif; ?>
      <button class="filter-btn active" id="toggle-all-filters">🔄 <span class="filter-text">ALLE</span></button>
      <button class="filter-btn active" data-filter="filter-gezin">🎂 <span class="filter-text">GEZIN</span></button>
      <button class="filter-btn active" data-filter="filter-familie">🎂 <span class="filter-text">FAMILIE</span></button>
      <button class="filter-btn active" data-filter="filter-feestdagen">🎉 <span class="filter-text">FEESTDAGEN</span></button>
      <button class="filter-btn active" data-filter="filter-sport">⚽ <span class="filter-text">SPORT</span></button>
      <button class="filter-btn active" data-filter="filter-andere">📅 <span class="filter-text">ANDERE</span></button>
    </div>

    <div class="months-grid">
      <?php
      $fullMonthNames = [
          1 => 'Januari', 2 => 'Februari', 3 => 'Maart', 4 => 'April',
          5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Augustus',
          9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'December'
      ];

      for ($m = 1; $m <= 12; $m++):
        $isActive = ($m === $currentMonth) ? 'active-month' : '';
        $monthEvents = $eventsByMonth[$m];

        // Sort events within the month by day (since they are currently sorted globally by daysRemaining,
        // which can mix next year vs this year if we just look at the list).
        // Best to sort strictly by date of month for display inside the month box.
        usort($monthEvents, function($a, $b) {
            return (int)$a['nextDate']->format('j') <=> (int)$b['nextDate']->format('j');
        });
      ?>
      <details class="month-details <?php echo $isActive; ?>" data-month="<?php echo $m; ?>">
        <summary class="month-summary">
          <div class="month-title"><?php echo $fullMonthNames[$m]; ?></div>
          <div class="month-count"><?php echo count($monthEvents); ?></div>
        </summary>
        <div class="month-content">
          <?php if (count($monthEvents) > 0): ?>
            <div class="events-list">
              <?php foreach ($monthEvents as $e):
                  $cat = strtolower($e['original']['category'] ?? '');
                  $icon = '📅';
                  if ($cat === 'verjaardag') $icon = '🎂';
                  elseif ($cat === 'huwelijk') $icon = '💍';
                  elseif ($cat === 'feestdag') $icon = '🎉';
                  elseif ($cat === 'sport') $icon = '⚽';
              ?>
              <div class="event-row" data-filter="<?php echo $e['filterClass']; ?>">
                <div class="er-icon"><?php echo $icon; ?></div>
                <div class="er-main">
                  <div class="er-name-wrap">
                    <span class="cat-label cat-<?php echo htmlspecialchars($e['category']); ?>"><?php echo strtoupper(htmlspecialchars($e['category'])); ?>:</span>
                    <span class="er-name"><?php echo htmlspecialchars($e['original']['name']); ?></span>
                  </div>
                  <div class="er-meta">
                    <span class="er-date"><?php echo $e['formattedDate']; ?></span>
                    <?php if (!empty($e['info'])): ?>
                      <span class="msg-highlight" style="color:var(--text-muted);">&bull; <?php echo htmlspecialchars($e['info']); ?></span>
                    <?php endif; ?>
                    <?php if ($e['highlightMessage']): ?>
                      <span class="msg-highlight">&bull; <?php echo $e['highlightMessage']; ?></span>
                    <?php endif; ?>
                  </div>
                </div>
                <div class="er-days">
                  <?php
                    if (!$e['hasPassedThisYear']) {
                        if ($e['daysRemaining'] == 0) echo '<span style="color:var(--ok); font-weight:bold;">Vandaag</span>';
                        elseif ($e['daysRemaining'] == 1) echo 'Morgen';
                        else echo 'Binnen ' . $e['daysRemaining'] . ' dagen';
                    }
                  ?>
                </div>
              </div>
              <?php endforeach; ?>
            </div>
          <?php else: ?>
            <div class="no-events">Geen gelegenheden</div>
          <?php endif; ?>
        </div>
      </details>
      <?php endfor; ?>
    </div>

  </div>

  <div class="right">
    <?php include 'sidebar.php'; ?>
  </div>
</main>

<!-- We still include ha_core_js.php as requested to maintain global context -->
<script src="ha_core_js.php"></script>

<script>
  function tick() {
    const clockEl = document.getElementById('clock');
    if (clockEl) {
      clockEl.textContent = new Date().toLocaleTimeString('nl-BE', { hour12: false });
    }
  }
  tick();
  setInterval(tick, 1000);

  document.addEventListener('DOMContentLoaded', () => {
    const filterBtns = document.querySelectorAll('.filter-btn');

    // Load saved filters
    const savedFilters = localStorage.getItem('speciale_dagen_filters');
    if (savedFilters) {
      try {
        const filtersArray = JSON.parse(savedFilters);
        // Reset all filter buttons (except toggle-all)
        document.querySelectorAll('.filter-btn[data-filter]').forEach(btn => {
          if (filtersArray.includes(btn.dataset.filter)) {
            btn.classList.add('active');
          } else {
            btn.classList.remove('active');
          }
        });
      } catch(e) {}
    }

    // Load saved open months
    const savedMonths = localStorage.getItem('speciale_dagen_months_open');
    if (savedMonths) {
      try {
        const monthsArray = JSON.parse(savedMonths);
        document.querySelectorAll('.month-details').forEach(month => {
          if (month.dataset.month && monthsArray.includes(month.dataset.month)) {
            month.open = true;
          }
        });
      } catch(e) {}
    }

    function applyFilters() {
      // Get all active filters
      const activeFilters = Array.from(document.querySelectorAll('.filter-btn.active'))
                                 .map(btn => btn.dataset.filter)
                                 .filter(f => f); // filter out undefined from toggle-all

      // Save to localStorage
      localStorage.setItem('speciale_dagen_filters', JSON.stringify(activeFilters));

      // Filter upcoming cards items
      document.querySelectorAll('.upcoming-card').forEach(card => {
        let hasVisibleItem = false;
        card.querySelectorAll('.uc-item').forEach(item => {
          const itemFilter = item.dataset.filter;
          if (activeFilters.includes(itemFilter)) {
            item.style.display = 'flex';
            hasVisibleItem = true;
          } else {
            item.style.display = 'none';
          }
        });
        card.style.display = hasVisibleItem ? 'flex' : 'none';
      });

      // Filter month grids items
      document.querySelectorAll('.month-details').forEach(month => {
        let visibleCount = 0;
        month.querySelectorAll('.event-row').forEach(row => {
          const itemFilter = row.dataset.filter;
          if (activeFilters.includes(itemFilter)) {
            row.style.display = 'flex';
            visibleCount++;
          } else {
            row.style.display = 'none';
          }
        });

        const countBadge = month.querySelector('.month-count');
        if (countBadge) {
            countBadge.textContent = visibleCount;
        }
      });

      // Update the toggle-all button state
      const toggleAllBtn = document.getElementById('toggle-all-filters');
      if (toggleAllBtn) {
        const catBtns = Array.from(document.querySelectorAll('.filter-btn[data-filter]'));
        const allActive = catBtns.every(b => b.classList.contains('active'));
        if (allActive) {
          toggleAllBtn.classList.add('active');
        } else {
          toggleAllBtn.classList.remove('active');
        }
      }
    }

    filterBtns.forEach(btn => {
      if (btn.id === 'toggle-all-filters') {
        btn.addEventListener('click', () => {
          const isTurningOn = !btn.classList.contains('active');
          document.querySelectorAll('.filter-btn[data-filter]').forEach(catBtn => {
            if (isTurningOn) {
              catBtn.classList.add('active');
            } else {
              catBtn.classList.remove('active');
            }
          });
          applyFilters();
        });
      } else {
        btn.addEventListener('click', () => {
          btn.classList.toggle('active');
          applyFilters();
        });
      }
    });

    // Save open months on toggle
    document.querySelectorAll('.month-details').forEach(month => {
      month.addEventListener('toggle', () => {
        const openMonths = Array.from(document.querySelectorAll('.month-details[open]'))
                                .map(m => m.dataset.month)
                                .filter(m => m);
        localStorage.setItem('speciale_dagen_months_open', JSON.stringify(openMonths));
      });
    });

    // Initial apply
    applyFilters();
  });
</script>
</body>
</html>
