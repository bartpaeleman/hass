<?php
require_once 'config.php';
require_once 'auth.php';

$html = @file_get_contents("https://pouletips.nl/wk-2026/uitslagen/");
$groupData = [];
$knockoutData = [];

$countryFlags = [
    'Algerije' => '🇩🇿', 'Argentinië' => '🇦🇷', 'Australië' => '🇦🇺', 'België' => '🇧🇪',
    'Bosnië-Herzegovina' => '🇧🇦', 'Brazilië' => '🇧🇷', 'Canada' => '🇨🇦', 'Colombia' => '🇨🇴',
    'Curaçao' => '🇨🇼', 'DR Congo' => '🇨🇩', 'Duitsland' => '🇩🇪', 'Ecuador' => '🇪🇨',
    'Egypte' => '🇪🇬', 'Frankrijk' => '🇫🇷', 'Ghana' => '🇬🇭', 'Haïti' => '🇭🇹',
    'Irak' => '🇮🇶', 'Iran' => '🇮🇷', 'Ivoorkust' => '🇨🇮', 'Japan' => '🇯🇵',
    'Jordanië' => '🇯🇴', 'Kaapverdië' => '🇨🇻', 'Kroatië' => '🇭🇷', 'Marokko' => '🇲🇦',
    'Mexico' => '🇲🇽', 'Nederland' => '🇳🇱', 'Nieuw-Zeeland' => '🇳🇿', 'Noorwegen' => '🇳🇴',
    'Oezbekistan' => '🇺🇿', 'Oostenrijk' => '🇦🇹', 'Panama' => '🇵🇦', 'Paraguay' => '🇵🇾',
    'Portugal' => '🇵🇹', 'Qatar' => '🇶🇦', 'Saudi-Arabië' => '🇸🇦', 'Senegal' => '🇸🇳',
    'Spanje' => '🇪🇸', 'Tsjechië' => '🇨🇿', 'Tunesië' => '🇹🇳', 'Turkije' => '🇹🇷',
    'Uruguay' => '🇺🇾', 'Verenigde Staten' => '🇺🇸', 'Zuid-Afrika' => '🇿🇦',
    'Zuid-Korea' => '🇰🇷', 'Zweden' => '🇸🇪', 'Zwitserland' => '🇨🇭',
    'Schotland' => '🏴󠁧󠁢󠁳󠁣󠁴󠁿', 'Engeland' => '🏴󠁧󠁢󠁥󠁮󠁧󠁿',
];

$stadiums = [
    'Los Angeles', 'Boston', 'Monterrey', 'Houston', 'New York/New Jersey',
    'Dallas', 'Mexico City', 'Atlanta', 'San Francisco', 'Seattle', 'Toronto',
    'Vancouver', 'Miami', 'Kansas City', 'Philadelphia', 'Guadalajara'
];

function parseMatchStr($str, $stadiums) {
    $parts = explode(' · ', $str);
    if (count($parts) === 2) {
        $time = trim($parts[0]);
        $rest = trim($parts[1]);

        $foundStadium = "";
        foreach ($stadiums as $s) {
            if (strpos($rest, $s) === 0) {
                $foundStadium = $s;
                $rest = trim(substr($rest, strlen($s)));
                break;
            }
        }

        if (preg_match('/^(.*?)\s+((?:\d+|-) \s*(?:-|:)\s* (?:\d+|-))\s+(voorspelling\s+)?(.*)$/ix', $rest, $m)) {
            return [
                'time' => $time,
                'stadium' => $foundStadium,
                'team1' => trim($m[1]),
                'score' => trim($m[2]),
                'isForecast' => !empty(trim($m[3])),
                'team2' => trim($m[4])
            ];
        }
    }
    return null;
}

if ($html !== false) {
    $dom = new DOMDocument();
    @$dom->loadHTML($html);
    $xpath = new DOMXPath($dom);

    $groupPhaseDiv = $xpath->query('/html/body/main/section/div[3]');
    if ($groupPhaseDiv->length > 0) {
        $divs = $xpath->query('./div', $groupPhaseDiv->item(0));
        foreach ($divs as $div) {
            $h2 = $xpath->query('.//h2', $div)->item(0);
            $sectionName = $h2 ? trim($h2->textContent) : 'Onbekend';
            $groupData[$sectionName] = [];
            $matches = $xpath->query('.//a', $div);
            foreach($matches as $m) {
                $matchText = trim(preg_replace('/\s+/', ' ', $m->textContent));
                if (strpos($matchText, 'Analyse') === false) {
                    $parsed = parseMatchStr($matchText, $stadiums);
                    if ($parsed) $groupData[$sectionName][] = $parsed;
                }
            }
        }
    }

    $knockoutDiv = $xpath->query('/html/body/main/section/div[4]/div');
    if ($knockoutDiv->length > 0) {
        $divs = $xpath->query('./div', $knockoutDiv->item(0));
        foreach ($divs as $div) {
            $h3 = $xpath->query('.//h3 | .//h2', $div)->item(0);
            $phaseName = $h3 ? trim($h3->textContent) : 'Onbekend';
            $knockoutData[$phaseName] = [];
            $matches = $xpath->query('.//a', $div);
            foreach($matches as $m) {
                $matchText = trim(preg_replace('/\s+/', ' ', $m->textContent));
                $parsed = parseMatchStr($matchText, $stadiums);
                if ($parsed) $knockoutData[$phaseName][] = $parsed;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>WK VOETBAL 2026 UITSLAGEN</title>
<link href="https://fonts.googleapis.com/css2?family=Share+Tech+Mono&family=Barlow+Condensed:wght@300;400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="CSS/common.css">
<link rel="stylesheet" href="CSS/wk2026.css">
<link rel="stylesheet" href="CSS/wk2026poules.css">
<style>
  .match-row {
      display: flex;
      flex-direction: column;
      padding: 12px;
      border-bottom: 1px solid rgba(255,255,255,0.05);
  }
  .match-row:last-child {
      border-bottom: none;
  }
  .match-meta {
      font-size: 12px;
      color: var(--text-muted);
      margin-bottom: 8px;
      font-family: 'Share Tech Mono', monospace;
  }
  .match-teams {
      display: flex;
      justify-content: space-between;
      align-items: center;
      font-size: 16px;
      font-weight: 600;
      color: var(--text-bright);
  }
  .match-team {
      flex: 1;
      display: flex;
      align-items: center;
      gap: 8px;
  }
  .match-team.right-align {
      justify-content: flex-end;
      text-align: right;
  }
  .match-score {
      font-family: 'Share Tech Mono', monospace;
      font-size: 18px;
      padding: 4px 12px;
      background: rgba(255,255,255,0.1);
      border-radius: 4px;
      min-width: 60px;
      text-align: center;
      margin: 0 15px;
  }
  .is-forecast {
      color: var(--warn) !important;
  }
  .is-forecast .match-score {
      background: rgba(255, 160, 0, 0.1);
      border: 1px solid var(--warn);
  }
  .forecast-badge {
      font-size: 10px;
      background: var(--warn);
      color: #000;
      padding: 2px 6px;
      border-radius: 4px;
      margin-left: 8px;
      vertical-align: middle;
      text-transform: uppercase;
      font-weight: bold;
  }
  .phase-header {
      font-family: 'Share Tech Mono', monospace;
      color: var(--accent);
      font-size: 24px;
      margin: 30px 0 15px 0;
      border-bottom: 2px solid var(--accent);
      padding-bottom: 5px;
  }
</style>
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
      <span>UITSLAGEN & VOORSPELLINGEN</span>
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

    <div class="filters-container" style="margin-top: 20px;">
      <a href="wk2026.php" class="filter-btn">⬅️ <span class="filter-text">Dashboard</span></a>
    </div>

    <?php if (empty($groupData) && empty($knockoutData)): ?>
      <p style="color: var(--warn); padding: 20px;">Kon de uitslagen niet inladen. Probeer het later opnieuw.</p>
    <?php else: ?>

      <?php if (!empty($groupData)): ?>
      <div class="phase-header">GROEPSFASE</div>
      <div class="poules-grid">
        <?php foreach ($groupData as $groupName => $matches): ?>
          <details class="poule-details" open>
            <summary class="poule-summary">
              <div class="month-title"><?php echo htmlspecialchars($groupName); ?></div>
            </summary>
            <div class="poule-content" style="padding: 0;">
              <?php foreach ($matches as $match):
                  $t1Flag = $countryFlags[$match['team1']] ?? '';
                  $t2Flag = $countryFlags[$match['team2']] ?? '';
                  $isF = $match['isForecast'];
              ?>
              <div class="match-row <?php echo $isF ? 'is-forecast' : ''; ?>">
                  <div class="match-meta">
                      <?php echo htmlspecialchars($match['time']); ?>
                      <?php if ($match['stadium']) echo ' • ' . htmlspecialchars($match['stadium']); ?>
                      <?php if ($isF) echo '<span class="forecast-badge">Voorspelling</span>'; ?>
                  </div>
                  <div class="match-teams">
                      <div class="match-team">
                          <?php echo $t1Flag; ?> <?php echo htmlspecialchars($match['team1']); ?>
                      </div>
                      <div class="match-score"><?php echo htmlspecialchars($match['score']); ?></div>
                      <div class="match-team right-align">
                          <?php echo htmlspecialchars($match['team2']); ?> <?php echo $t2Flag; ?>
                      </div>
                  </div>
              </div>
              <?php endforeach; ?>
            </div>
          </details>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

      <?php if (!empty($knockoutData)): ?>
      <div class="phase-header">KNOCK-OUT FASE</div>
      <div class="poules-grid">
        <?php foreach ($knockoutData as $phaseName => $matches): ?>
          <details class="poule-details" open>
            <summary class="poule-summary">
              <div class="month-title"><?php echo htmlspecialchars($phaseName); ?></div>
            </summary>
            <div class="poule-content" style="padding: 0;">
              <?php foreach ($matches as $match):
                  $t1Flag = $countryFlags[$match['team1']] ?? '';
                  $t2Flag = $countryFlags[$match['team2']] ?? '';
                  $isF = $match['isForecast'];
              ?>
              <div class="match-row <?php echo $isF ? 'is-forecast' : ''; ?>">
                  <div class="match-meta">
                      <?php echo htmlspecialchars($match['time']); ?>
                      <?php if ($match['stadium']) echo ' • ' . htmlspecialchars($match['stadium']); ?>
                      <?php if ($isF) echo '<span class="forecast-badge">Voorspelling</span>'; ?>
                  </div>
                  <div class="match-teams">
                      <div class="match-team">
                          <?php echo $t1Flag; ?> <?php echo htmlspecialchars($match['team1']); ?>
                      </div>
                      <div class="match-score"><?php echo htmlspecialchars($match['score']); ?></div>
                      <div class="match-team right-align">
                          <?php echo htmlspecialchars($match['team2']); ?> <?php echo $t2Flag; ?>
                      </div>
                  </div>
              </div>
              <?php endforeach; ?>
            </div>
          </details>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

    <?php endif; ?>

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
</script>
</body>
</html>
