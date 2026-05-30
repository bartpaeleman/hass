<?php
require_once 'config.php';

// Data processing logic
$eventsFile = 'events.json';
$events = [];
if (file_exists($eventsFile)) {
    $eventsData = json_decode(file_get_contents($eventsFile), true);
    if (is_array($eventsData)) {
        $events = $eventsData;
    }
}

$today = new DateTime('today');
$processedEvents = [];

$months = [
    1 => 'jan', 2 => 'feb', 3 => 'mrt', 4 => 'apr',
    5 => 'mei', 6 => 'jun', 7 => 'jul', 8 => 'aug',
    9 => 'sep', 10 => 'okt', 11 => 'nov', 12 => 'dec'
];

foreach ($events as $event) {
    if (!isset($event['date']) || !isset($event['name'])) continue;

    try {
        $eventDate = new DateTime($event['date']);
    } catch (Exception $e) {
        continue;
    }

    $isRecurring = isset($event['type']) && $event['type'] === 'recurring';
    $nextDate = clone $eventDate;

    if ($isRecurring) {
        $nextDate->setDate($today->format('Y'), $eventDate->format('m'), $eventDate->format('d'));
        if ($nextDate < $today) {
            $nextDate->modify('+1 year');
        }
    } else {
        // For single events, if it's past, we might still show it or skip it. Let's show it if it's today or in the future.
        // If it's strictly in the past, maybe skip? Let's skip past single events.
        $nextDate->setTime(0,0,0);
        if ($nextDate < $today) continue;
    }

    $interval = $today->diff($nextDate);
    $daysRemaining = (int)$interval->format('%a');

    // Calculate years if applicable (for birthdays and weddings)
    $currentYears = null;
    $nextYears = null;
    $isBirthdayOrWedding = isset($event['category']) && in_array(strtolower($event['category']), ['verjaardag', 'huwelijk']);

    if ($isRecurring && $isBirthdayOrWedding) {
        $currentYears = $today->format('Y') - $eventDate->format('Y');
        // If the date hasn't happened yet this year, they haven't reached the "current" age for this year yet
        $thisYearDate = clone $eventDate;
        $thisYearDate->setDate($today->format('Y'), $eventDate->format('m'), $eventDate->format('d'));
        if ($thisYearDate > $today) {
            $currentYears--;
        }
        $nextYears = $nextDate->format('Y') - $eventDate->format('Y');
    }

    $processedEvents[] = [
        'original' => $event,
        'nextDate' => $nextDate,
        'daysRemaining' => $daysRemaining,
        'formattedDate' => $nextDate->format('j') . ' ' . $months[(int)$nextDate->format('n')],
        'currentYears' => $currentYears,
        'nextYears' => $nextYears
    ];
}

// Sort by days remaining
usort($processedEvents, function($a, $b) {
    return $a['daysRemaining'] <=> $b['daysRemaining'];
});

$upcomingEvents = array_filter($processedEvents, function($e) {
    return $e['daysRemaining'] <= 7;
});

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

    <!-- Aankomende Speciale Dagen (Max 1 week) -->
    <?php if (count($upcomingEvents) > 0): ?>
    <div class="section-label" style="margin-bottom:12px;">
      <svg width="12" height="12" viewBox="0 0 12 12" fill="none" style="vertical-align: middle; margin-right:4px;"><path d="M6 1v6.5M6 10a2 2 0 100-4 2 2 0 000 4z" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/><path d="M8 3h1M8 5h1" stroke="currentColor" stroke-width="1" stroke-linecap="round"/></svg>
      Komende Week (7 dagen)
    </div>
    <div class="upcoming-grid">
      <?php foreach ($upcomingEvents as $e):
          $cat = strtolower($e['original']['category'] ?? '');
          $icon = '📅';
          if ($cat === 'verjaardag') $icon = '🎂';
          elseif ($cat === 'huwelijk') $icon = '💍';
          elseif ($cat === 'feestdag') $icon = '🎉';

          $dayText = '';
          if ($e['daysRemaining'] == 0) $dayText = 'Vandaag!';
          elseif ($e['daysRemaining'] == 1) $dayText = 'Morgen';
          elseif ($e['daysRemaining'] == 2) $dayText = 'Overmorgen';
          else $dayText = 'Over ' . $e['daysRemaining'] . ' dagen';
      ?>
      <div class="upcoming-card">
        <div class="uc-icon"><?php echo $icon; ?></div>
        <div class="uc-details">
          <div class="uc-name"><?php echo htmlspecialchars($e['original']['name']); ?></div>
          <div class="uc-date"><?php echo $dayText; ?> (<?php echo $e['formattedDate']; ?>)</div>
          <?php if ($e['nextYears'] !== null): ?>
            <div class="uc-years"><?php echo $e['currentYears']; ?> <span style="color:var(--dim)">→</span> <?php echo $e['nextYears']; ?> jaar</div>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Alle Speciale Dagen -->
    <div class="section-label" style="margin-top:24px; margin-bottom:12px;">
      <svg width="12" height="12" viewBox="0 0 12 12" fill="none" style="vertical-align: middle; margin-right:4px;"><path d="M2 3h8M2 6h8M2 9h8" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/></svg>
      Alle Gelegenheden
    </div>
    <div class="events-list">
      <?php if (count($processedEvents) > 0): ?>
        <?php foreach ($processedEvents as $e):
            $cat = strtolower($e['original']['category'] ?? '');
            $icon = '📅';
            if ($cat === 'verjaardag') $icon = '🎂';
            elseif ($cat === 'huwelijk') $icon = '💍';
            elseif ($cat === 'feestdag') $icon = '🎉';
        ?>
        <div class="event-row">
          <div class="er-icon"><?php echo $icon; ?></div>
          <div class="er-main">
            <div class="er-name"><?php echo htmlspecialchars($e['original']['name']); ?></div>
            <div class="er-meta">
              <span class="er-date"><?php echo $e['formattedDate']; ?></span>
              <?php if ($e['nextYears'] !== null): ?>
                <span class="er-years">&bull; <?php echo $e['currentYears']; ?> <span style="color:var(--dim)">→</span> <?php echo $e['nextYears']; ?> jaar</span>
              <?php endif; ?>
            </div>
          </div>
          <div class="er-days">
            <?php
              if ($e['daysRemaining'] == 0) echo '<span style="color:var(--ok); font-weight:bold;">Vandaag</span>';
              elseif ($e['daysRemaining'] == 1) echo 'Morgen';
              else echo $e['daysRemaining'] . ' dgn';
            ?>
          </div>
        </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div style="padding:16px; color:var(--text-muted); text-align:center; background:var(--surface); border:1px solid var(--border); border-radius:6px;">Geen gelegenheden gevonden.</div>
      <?php endif; ?>
    </div>

  </div>

  <div class="right">
    <?php include 'sidebar.php'; ?>
  </div>
</main>

<!-- We still include ha_core_js.php as requested to maintain global context -->
<script src="ha_core_js.php"></script>

</body>
</html>
