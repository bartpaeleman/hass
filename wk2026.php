<?php
require_once 'config.php';

// Data processing logic
$eventsFile = __DIR__ . '/JSON/wk2026.json';
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
    $days = easter_days($year);
    $date = new DateTime("$year-03-21");
    $date->modify("+$days days");
    return $date;
}

function getSpecificWeekday($year, $occurrence, $dayOfWeek, $month) {
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

    $filterClass = 'filter-sport';
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
    return $e['daysRemaining'] >= 0 && $e['daysRemaining'] <= 1;
});

// Group upcoming events by daysRemaining
$upcomingGrouped = [];
foreach ($upcomingEventsRaw as $e) {
    $d = $e['daysRemaining'];
    if (!isset($upcomingGrouped[$d])) {
        $upcomingGrouped[$d] = [];
    }
    $upcomingGrouped[$d][] = $e;
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
<link rel="stylesheet" href="CSS/kalender.css">
<link rel="stylesheet" href="CSS/wk2026.css">
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

    <?php if (count($upcomingGrouped) > 0): ?>
    <div class="section-label section-label-upcoming">
      <svg width="12" height="12" viewBox="0 0 12 12" fill="none" style="vertical-align: middle; margin-right:4px;"><path d="M6 1v6.5M6 10a2 2 0 100-4 2 2 0 000 4z" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/><path d="M8 3h1M8 5h1" stroke="currentColor" stroke-width="1" stroke-linecap="round"/></svg>
      Vandaag & Morgen
    </div>
    <div class="upcoming-grid">
      <?php
      ksort($upcomingGrouped);
      foreach ($upcomingGrouped as $daysRemaining => $eventsInCat):
          $dayText = '';
          $borderColor = 'var(--accent)';
          if ($daysRemaining == 0) {
              $dayText = 'Vandaag!';
              $borderColor = 'var(--ok)';
          } elseif ($daysRemaining == 1) {
              $dayText = 'Morgen';
              $borderColor = 'var(--warn)';
          }

          $icon = '⚽';
          $formattedDate = $eventsInCat[0]['formattedDate'];
      ?>
      <div class="upcoming-card wk-upcoming-card" style="border-color: <?php echo $borderColor; ?>">
        <div class="uc-icon"><?php echo $icon; ?></div>
        <div class="uc-details wk-uc-details">
          <div class="uc-date wk-uc-date"><?php echo $dayText; ?> (<?php echo $formattedDate; ?>)</div>
          <div class="uc-items-list wk-uc-items-list">
            <?php
            $count = count($eventsInCat);
            $i = 0;
            foreach ($eventsInCat as $e):
                $i++;
                $time = '';
                if (!empty($e['starttime']) && preg_match('/Aftrap\s+(\d{2}u\d{2})/', $e['starttime'], $matches)) {
                    $time = $matches[1] . ': ';
                } elseif (!empty($e['starttime'])) {
                    $time = str_replace('⏱️ Aftrap ', '', $e['starttime']) . ': ';
                }
            ?>
              <div class="uc-item wk-uc-item" data-filter="<?php echo $e['filterClass']; ?>">
                <div class="uc-name-wrap wk-uc-name-wrap">
                  <span class="uc-name wk-uc-name"><?php echo htmlspecialchars($time) . htmlspecialchars($e['original']['name']); ?></span>
                </div>
              </div>
              <?php if ($i < $count): ?>
                <hr class="wk-uc-divider">
              <?php endif; ?>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div class="section-label section-label-events">
      <svg width="12" height="12" viewBox="0 0 12 12" fill="none" style="vertical-align: middle; margin-right:4px;"><path d="M2 3h8M2 6h8M2 9h8" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/></svg>
      Events
    </div>

    <div class="filters-container">
      <?php if (isset($_SESSION['role_level']) && $_SESSION['role_level'] >= 50): ?>
      <a href="ADMIN/wk_admin.php?json-events=wk2026.json" class="filter-btn admin-btn">⚙️ <span class="filter-text">BEHEER</span></a>
      <?php endif; ?>
    </div>

    <div class="months-wrapper">
      <?php
      $fullMonthNames = [
          6 => 'Juni', 7 => 'Juli'
      ];

      for ($m = 6; $m <= 7; $m++):
        $monthEvents = $eventsByMonth[$m] ?? [];

        usort($monthEvents, function($a, $b) {
            $dateCmp = (int)$a['nextDate']->format('j') <=> (int)$b['nextDate']->format('j');
            if ($dateCmp !== 0) return $dateCmp;

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

        $weeks = [];
        foreach ($monthEvents as $e) {
            $weekNum = $e['nextDate']->format('W');
            $dayStr = $e['formattedDate'];

            if (!isset($weeks[$weekNum])) {
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
      <details class="month-details active-month" data-month="<?php echo $m; ?>" open>
        <summary class="month-summary">
          <div class="month-title"><?php echo $fullMonthNames[$m]; ?></div>
          <div class="month-count"><?php echo count($monthEvents); ?></div>
        </summary>
        <div class="month-content">
          <?php if (count($monthEvents) > 0): ?>
            <div class="weeks-grid">
            <?php foreach ($weeks as $weekNum => $weekData): ?>
              <details class="week-details">
                <summary class="week-summary"><?php echo $weekData['label']; ?></summary>
                <div class="week-content">
                  <?php
                  $currentRonde = '';
                  foreach ($weekData['days'] as $dayStr => $dayEvents): ?>
                    <div class="day-container">
                      <h3 class="day-title"><?php echo $dayStr; ?></h3>
                      <div class="match-grid">
                        <?php foreach ($dayEvents as $e):
                            $icon = '⚽';
                        ?>
                        <?php if ($e['ronde'] !== $currentRonde):
                            $currentRonde = $e['ronde'];
                        ?>
                            <div class="ronde-divider">
                                <?php echo htmlspecialchars($currentRonde); ?>
                            </div>
                        <?php endif; ?>

                        <div class="event-row">
                          <div class="er-icon"><?php echo $icon; ?></div>
                          <div class="er-main">
                            <div class="er-name-wrap">
                              <span class="er-name"><?php echo htmlspecialchars($e['original']['name']); ?></span>
                            </div>
                            <div class="er-meta">
                              <?php if (!empty($e['starttime'])): ?>
                                <span class="msg-highlight er-msg"><?php echo htmlspecialchars($e['starttime']); ?></span>
                              <?php endif; ?>
                              <?php if (!empty($e['stadion'])): ?>
                                <span class="msg-highlight er-msg"><?php echo htmlspecialchars($e['stadion']); ?></span>
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

    document.querySelectorAll('.month-details').forEach(month => {
      month.addEventListener('toggle', () => {
        const openMonths = Array.from(document.querySelectorAll('.month-details[open]'))
                                .map(m => m.dataset.month)
                                .filter(m => m);
        localStorage.setItem('speciale_dagen_months_open', JSON.stringify(openMonths));
      });
    });
  });
</script>
</body>
</html>
