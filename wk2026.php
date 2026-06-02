<?php
require_once 'config.php';

// Data processing logic
$eventsFile = __DIR__ . '/JSON/wk2026.json';
$events = [];
if (file_exists($eventsFile)) {
    $eventsData = json_decode(file_get_contents($eventsFile), true);
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

$months = [
    1 => 'jan', 2 => 'feb', 3 => 'mrt', 4 => 'apr',
    5 => 'mei', 6 => 'jun', 7 => 'jul', 8 => 'aug',
    9 => 'sep', 10 => 'okt', 11 => 'nov', 12 => 'dec'
];

foreach ($events as $event) {
    if (!isset($event['name']) || !isset($event['date'])) continue;

    $nextDate = new DateTime($event['date']);
    $nextDate->setTime(0,0,0);

    $hasPassedThisYear = false;
    if ($nextDate < clone($today)->setTime(0,0,0)) {
        $hasPassedThisYear = true;
    }

    $interval = $today->diff($nextDate);
    $daysRemaining = $interval->invert ? -$interval->days : $interval->days;

    $formattedDate = $nextDate->format('j') . ' ' . $months[(int)$nextDate->format('n')];

    // New format: ronde, starttime, stadion
    $ronde = $event['ronde'] ?? '';
    $starttime = $event['starttime'] ?? '';
    $stadion = $event['stadion'] ?? '';

    // Calculate filterClass based on something if needed, but we don't have filters anymore
    $filterClass = 'filter-sport';

    // Highlight message
    $highlightMessage = '';

    $processedEvents[] = [
        'original' => $event,
        'nextDate' => $nextDate,
        'daysRemaining' => $daysRemaining,
        'formattedDate' => $formattedDate,
        'highlightMessage' => $highlightMessage,
        'hasPassedThisYear' => $hasPassedThisYear,
        'filterClass' => $filterClass,
        'ronde' => $ronde,
        'starttime' => $starttime,
        'stadion' => $stadion
    ];
}

// Sort by days remaining (global) - for upcoming events
usort($processedEvents, function($a, $b) {
    return $a['daysRemaining'] <=> $b['daysRemaining'];
});

$upcomingEventsRaw = array_filter($processedEvents, function($e) {
    return $e['daysRemaining'] >= 0 && $e['daysRemaining'] <= 5;
});

// Group upcoming events by daysRemaining -> ronde
$upcomingGrouped = [];
foreach ($upcomingEventsRaw as $e) {
    $d = $e['daysRemaining'];
    $cat = $e['ronde']; // Using ronde instead of category for upcoming
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
<title>WK VOETBAL 2026</title>
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
      <h1>WK VOETBAL 2026</h1>
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
              $icon = '⚽';

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
                  <span class="cat-label cat-sport"><?php echo strtoupper(htmlspecialchars($e['ronde'])); ?>:</span>
                  <span class="uc-name"><?php echo htmlspecialchars($e['original']['name']); ?></span>
                  <?php if (!empty($e['starttime'])): ?>
                    <span style="color:var(--text-muted); font-size: 0.9em; margin-left: 5px;">- <?php echo htmlspecialchars($e['starttime']); ?></span>
                  <?php endif; ?>
                </div>
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
      <a href="ADMIN/wk_admin.php?json-events=wk2026.json" class="filter-btn" style="text-decoration: none; border-color: var(--warn); color: var(--warn);">⚙️ <span class="filter-text">BEHEER</span></a>
      <?php endif; ?>
    </div>

    <div class="months-wrapper" style="display: flex; flex-direction: column; gap: 16px;">
      <?php
      $fullMonthNames = [
          6 => 'Juni', 7 => 'Juli'
      ];

      for ($m = 6; $m <= 7; $m++):
        $monthEvents = $eventsByMonth[$m] ?? [];

        // Sort events within the month by day and time
        usort($monthEvents, function($a, $b) {
            $dateCmp = (int)$a['nextDate']->format('j') <=> (int)$b['nextDate']->format('j');
            if ($dateCmp !== 0) return $dateCmp;

            // Try to extract time from starttime (e.g. "⏱️ Aftrap 21:00")
            $timeA = "00:00";
            if (preg_match('/Aftrap\s+(\d{2}:\d{2})/', $a['starttime'], $matches)) {
                $timeA = $matches[1];
            }
            $timeB = "00:00";
            if (preg_match('/Aftrap\s+(\d{2}:\d{2})/', $b['starttime'], $matches)) {
                $timeB = $matches[1];
            }
            return strcmp($timeA, $timeB);
        });

        // Group by week, then by day
        $weeks = [];
        foreach ($monthEvents as $e) {
            $weekNum = $e['nextDate']->format('W');
            $dayStr = $e['formattedDate']; // Use formatted date as the day key

            if (!isset($weeks[$weekNum])) {
                // Calculate Monday and Sunday dates for this week
                $year = $e['nextDate']->format('Y');
                $dto = new DateTime();
                $dto->setISODate($year, $weekNum);
                $monday = $dto->format('j') . ' ' . $months[(int)$dto->format('n')];
                $dto->modify('+6 days');
                $sunday = $dto->format('j') . ' ' . $months[(int)$dto->format('n')];

                $weeks[$weekNum] = [
                    'label' => "WEEK $weekNum: $monday - $sunday",
                    'days' => []
                ];
            }
            if (!isset($weeks[$weekNum]['days'][$dayStr])) {
                $weeks[$weekNum]['days'][$dayStr] = [];
            }
            $weeks[$weekNum]['days'][$dayStr][] = $e;
        }
      ?>
      <details class="month-details active-month" data-month="<?php echo $m; ?>" open style="margin-bottom: 20px;">
        <summary class="month-summary">
          <div class="month-title"><?php echo $fullMonthNames[$m]; ?></div>
          <div class="month-count"><?php echo count($monthEvents); ?></div>
        </summary>
        <div class="month-content">
          <?php if (count($monthEvents) > 0): ?>
            <div class="weeks-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
            <?php foreach ($weeks as $weekNum => $weekData): ?>
              <details class="week-details" style="margin-bottom: 15px; border: 1px solid var(--border); border-radius: 6px; background: var(--bg);">
                <summary style="padding: 10px; cursor: pointer; font-family: 'Share Tech Mono', monospace; color: var(--text-bright); background: rgba(255,255,255,0.05); border-bottom: 1px solid var(--border);"><?php echo $weekData['label']; ?></summary>
                <div class="week-content" style="padding: 10px;">
                  <?php
                  $currentRonde = '';
                  foreach ($weekData['days'] as $dayStr => $dayEvents): ?>
                    <div style="margin-bottom: 15px;">
                      <h3 style="font-family: 'Share Tech Mono', monospace; color: var(--warn); margin: 0 0 8px 0; font-size: 16px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 4px;"><?php echo $dayStr; ?></h3>
                      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                        <?php foreach ($dayEvents as $e):
                            $icon = '⚽';
                        ?>
                        <?php if ($e['ronde'] !== $currentRonde):
                            $currentRonde = $e['ronde'];
                        ?>
                            <div style="grid-column: 1 / -1; background: var(--warn); color: var(--bg); padding: 6px 10px; font-weight: bold; border-radius: 4px; text-transform: uppercase; margin-top: 10px; margin-bottom: 5px; text-align: center;">
                                <?php echo htmlspecialchars($currentRonde); ?>
                            </div>
                        <?php endif; ?>

                        <div class="event-row" style="margin:0; background: var(--surface); border: 1px solid var(--border); padding: 10px; border-radius: 6px; display: flex; align-items: center; gap: 10px;">
                          <div class="er-icon"><?php echo $icon; ?></div>
                          <div class="er-main" style="flex:1;">
                            <div class="er-name-wrap">
                              <span class="er-name" style="font-size: 16px; font-weight: 600; color: var(--text-bright);"><?php echo htmlspecialchars($e['original']['name']); ?></span>
                            </div>
                            <div class="er-meta" style="margin-top: 5px; line-height: 1.4; display: flex; flex-direction: column; gap: 3px;">
                              <?php if (!empty($e['starttime'])): ?>
                                <span class="msg-highlight" style="color:var(--text-muted); font-size: 12px;"><?php echo htmlspecialchars($e['starttime']); ?></span>
                              <?php endif; ?>
                              <?php if (!empty($e['stadion'])): ?>
                                <span class="msg-highlight" style="color:var(--text-muted); font-size: 12px;"><?php echo htmlspecialchars($e['stadion']); ?></span>
                              <?php endif; ?>
                            </div>
                          </div>
                        </div>
                        <?php endforeach; ?>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>
              </details>
            <?php endforeach; ?>
            </div>
          <?php else: ?>
            <div class="no-events">Geen wedstrijden in deze maand</div>
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
  });
</script>
</body>
</html>
