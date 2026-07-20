<?php
require_once 'config.php';
require_once 'auth.php';

// Sync handler
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'sync_uitslagen') {
    if (isset($_SESSION['role_level']) && $_SESSION['role_level'] >= 99) {
        $externalHtml = @file_get_contents("https://pouletips.nl/wk-2026/uitslagen/");
        if ($externalHtml !== false) {
            file_put_contents(__DIR__ . '/JSON/wk2026_uitslagen.html', $externalHtml);
        }
        // Redirect to avoid form resubmission
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit;
    }
}

// Check configuration for sync buttons
$configFile = __DIR__ . '/JSON/config_data.json';
$configData = file_exists($configFile) ? json_decode(file_get_contents($configFile), true) : [];
$showSyncButtons = !isset($configData['settings']['SHOW_WK2026_SYNC_BUTTONS']) || !empty($configData['settings']['SHOW_WK2026_SYNC_BUTTONS']);

// Read from local static file
$localFile = __DIR__ . '/JSON/wk2026_uitslagen.html';
$html = file_exists($localFile) ? file_get_contents($localFile) : false;

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

    $allH2s = $xpath->query('//h2');
    foreach ($allH2s as $h2) {
        $sectionName = trim($h2->textContent);
        if (strpos($sectionName, 'Groep') !== false) {
            $parentDiv = $h2->parentNode;
            if ($parentDiv) {
                $grandparent = $parentDiv->parentNode;
                if ($grandparent) {
                    $greatGrandparent = $grandparent->parentNode;
                    if ($greatGrandparent) {
                        $matches = $xpath->query('.//a', $greatGrandparent);
                        if ($matches->length > 0) {
                            $groupData[$sectionName] = [];
                            foreach($matches as $m) {
                                $matchText = trim(preg_replace('/\s+/', ' ', $m->textContent));
                                if (strpos($matchText, 'Analyse') === false) {
                                    $parsed = parseMatchStr($matchText, $stadiums);
                                    if ($parsed) $groupData[$sectionName][] = $parsed;
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    $allH3s = $xpath->query('//h3 | //h2');
    foreach ($allH3s as $h3) {
        $phaseName = trim($h3->textContent);
        if (in_array($phaseName, ['Ronde van 32', 'Achtste finale', 'Kwartfinale', 'Halve finale', 'Derde plaats', 'Finale'])) {
            $parentDiv = $h3->parentNode;
            if ($parentDiv) {
                $grandparent = $parentDiv->parentNode;
                if ($grandparent) {
                    $greatGrandparent = $grandparent->parentNode;
                    if ($greatGrandparent) {
                        $matches = $xpath->query('.//a', $greatGrandparent);
                        if ($matches->length > 0) {
                            $knockoutData[$phaseName] = [];
                            foreach($matches as $m) {
                                $matchText = trim(preg_replace('/\s+/', ' ', $m->textContent));
                                $parsed = parseMatchStr($matchText, $stadiums);
                                if ($parsed) $knockoutData[$phaseName][] = $parsed;
                            }
                        }
                    }
                }
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
  #btnTogglePredictions {
      color: var(--warn);
      border-color: var(--warn);
  }
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

    <div class="filters-container" style="margin-top: 20px; display: flex; gap: 8px;">
      <a href="wk2026.php" class="filter-btn">⬅️ <span class="filter-text">WK AGENDA</span></a>
      <a href="wk2026poules.php" class="filter-btn">🏆 <span class="filter-text">POULES</span></a>
      <button id="btnTogglePredictions" class="filter-btn">📈 <span class="filter-text">VOORSPELLINGEN</span></button>
      <?php if ($showSyncButtons && isset($_SESSION['authenticated_user']) && isset($_SESSION['role_level']) && $_SESSION['role_level'] >= 99): ?>
      <form method="POST" style="margin: 0; margin-left: auto;">
          <input type="hidden" name="action" value="sync_uitslagen">
          <button type="submit" class="filter-btn admin-btn" style="border: 1px solid var(--accent); background: rgba(0,255,0,0.1); cursor: pointer;" onclick="return confirm('Dit zal de lokale uitslagen overschrijven met data van pouletips.nl. Doorgaan?');">🔄 <span class="filter-text">SYNC UITSLAGEN</span></button>
      </form>
      <?php endif; ?>
    </div>

    <?php if (empty($groupData) && empty($knockoutData)): ?>
      <p style="color: var(--warn); padding: 20px;">Kon de uitslagen niet inladen. Probeer het later opnieuw.</p>
    <?php else: ?>

      <?php if (!empty($groupData)): ?>
      <details class="phase-details" data-phase="groepsfase" open>
      <summary class="phase-header" style="cursor: pointer;">GROEPSFASE</summary>
      <div class="poules-grid">
        <?php foreach ($groupData as $groupName => $matches):
            $groupTeams = [];
            $hasBelgium = false;
            $hasNetherlands = false;
            foreach ($matches as $match) {
                if ($match['team1']) $groupTeams[$match['team1']] = true;
                if ($match['team2']) $groupTeams[$match['team2']] = true;
                if ($match['team1'] === 'België' || $match['team2'] === 'België') $hasBelgium = true;
                if ($match['team1'] === 'Nederland' || $match['team2'] === 'Nederland') $hasNetherlands = true;
            }
            $flagsStr = '';
            foreach (array_keys($groupTeams) as $t) {
                if (isset($countryFlags[$t])) $flagsStr .= $countryFlags[$t] . ' ';
            }
            $groupClass = '';
            $summaryClass = '';
            if ($hasBelgium) {
                $groupClass .= ' highlight-belgium-group';
                $summaryClass .= ' highlight-belgium-summary';
            }
            if ($hasNetherlands) {
                $groupClass .= ' highlight-netherlands-group';
                $summaryClass .= ' highlight-netherlands-summary';
            }
        ?>
          <details class="poule-details <?php echo $groupClass; ?>" data-group-name="<?php echo htmlspecialchars($groupName); ?>" open>
            <summary class="poule-summary <?php echo $summaryClass; ?>">
              <div class="month-title"><?php echo htmlspecialchars($groupName) . ' ' . trim($flagsStr); ?></div>
            </summary>
            <div class="poule-content" style="padding: 0;">
              <?php foreach ($matches as $match):
                  $t1Flag = $countryFlags[$match['team1']] ?? '';
                  $t2Flag = $countryFlags[$match['team2']] ?? '';
                  $isF = $match['isForecast'];
                  $isBelgiumMatch = ($match['team1'] === 'België' || $match['team2'] === 'België');
                  $isNetherlandsMatch = ($match['team1'] === 'Nederland' || $match['team2'] === 'Nederland');

                  $rowClass = '';
                  if ($isBelgiumMatch) $rowClass .= ' highlight-belgium-row';
                  if ($isNetherlandsMatch) $rowClass .= ' highlight-netherlands-row';
              ?>
              <div class="match-row <?php echo $isF ? 'is-forecast' : ''; ?> <?php echo $rowClass; ?>">
                  <div class="match-meta">
                      <?php echo htmlspecialchars($match['time']); ?>
                      <?php if ($match['stadium']) echo ' • ' . htmlspecialchars($match['stadium']); ?>
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
      </details>
      <?php endif; ?>

      <?php if (!empty($knockoutData)): ?>
      <details class="phase-details" data-phase="knockout" open>
      <summary class="phase-header" style="cursor: pointer;">KNOCK-OUT FASE</summary>
      <div class="poules-grid">
        <?php foreach ($knockoutData as $phaseName => $matches):
            $groupTeams = [];
            $hasBelgium = false;
            $hasNetherlands = false;
            foreach ($matches as $match) {
                if ($match['team1']) $groupTeams[$match['team1']] = true;
                if ($match['team2']) $groupTeams[$match['team2']] = true;
                if ($match['team1'] === 'België' || $match['team2'] === 'België') $hasBelgium = true;
                if ($match['team1'] === 'Nederland' || $match['team2'] === 'Nederland') $hasNetherlands = true;
            }
            $flagsStr = '';
            foreach (array_keys($groupTeams) as $t) {
                if (isset($countryFlags[$t])) $flagsStr .= $countryFlags[$t] . ' ';
            }
            $groupClass = '';
            $summaryClass = '';
            if ($hasBelgium) {
                $groupClass .= ' highlight-belgium-group';
                $summaryClass .= ' highlight-belgium-summary';
            }
            if ($hasNetherlands) {
                $groupClass .= ' highlight-netherlands-group';
                $summaryClass .= ' highlight-netherlands-summary';
            }
        ?>
          <details class="poule-details <?php echo $groupClass; ?>" data-group-name="<?php echo htmlspecialchars($phaseName); ?>" open>
            <summary class="poule-summary <?php echo $summaryClass; ?>">
              <div class="month-title"><?php echo htmlspecialchars($phaseName) . ' ' . trim($flagsStr); ?></div>
            </summary>
            <div class="poule-content" style="padding: 0;">
              <?php foreach ($matches as $match):
                  $t1Flag = $countryFlags[$match['team1']] ?? '';
                  $t2Flag = $countryFlags[$match['team2']] ?? '';
                  $isF = $match['isForecast'];
                  $isBelgiumMatch = ($match['team1'] === 'België' || $match['team2'] === 'België');
                  $isNetherlandsMatch = ($match['team1'] === 'Nederland' || $match['team2'] === 'Nederland');

                  $rowClass = '';
                  if ($isBelgiumMatch) $rowClass .= ' highlight-belgium-row';
                  if ($isNetherlandsMatch) $rowClass .= ' highlight-netherlands-row';
              ?>
              <div class="match-row <?php echo $isF ? 'is-forecast' : ''; ?> <?php echo $rowClass; ?>">
                  <div class="match-meta">
                      <?php echo htmlspecialchars($match['time']); ?>
                      <?php if ($match['stadium']) echo ' • ' . htmlspecialchars($match['stadium']); ?>
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
      </details>
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

  document.addEventListener('DOMContentLoaded', () => {
    // Prediction toggle logic
    const btnToggle = document.getElementById('btnTogglePredictions');
    if (btnToggle) {
      let showPredictions = localStorage.getItem('wk2026_show_predictions') === 'true';
      const applyPredictionsState = () => {
        if (showPredictions) {
          document.body.classList.remove('hide-forecasts');
          btnToggle.style.opacity = '1';
          btnToggle.style.background = 'rgba(255, 160, 0, 0.1)';
        } else {
          document.body.classList.add('hide-forecasts');
          btnToggle.style.opacity = '0.6';
          btnToggle.style.background = 'transparent';
        }
      };

      applyPredictionsState(); // apply default state

      btnToggle.addEventListener('click', () => {
        showPredictions = !showPredictions;
        localStorage.setItem('wk2026_show_predictions', showPredictions);
        applyPredictionsState();
      });
    }

    // Persist details blocks state
    const detailsElements = document.querySelectorAll('details.poule-details');
    detailsElements.forEach(details => {
      const groupName = details.getAttribute('data-group-name');
      if (groupName) {
        const key = 'wk2026uitslagen_open_' + groupName;
        const storedState = localStorage.getItem(key);
        if (storedState !== null) {
          if (storedState === 'true') {
            details.setAttribute('open', '');
          } else {
            details.removeAttribute('open');
          }
        }

        details.addEventListener('toggle', (e) => {
          localStorage.setItem(key, details.open);

          if (window.innerWidth >= 900) {
              const parentGrid = details.closest('.poules-grid');
              if (parentGrid) {
                  const siblings = Array.from(parentGrid.querySelectorAll(':scope > details.poule-details'));
                  const index = siblings.indexOf(details);
                  let siblingIndex = -1;

                  if (index % 2 === 0 && index + 1 < siblings.length) {
                      siblingIndex = index + 1;
                  } else if (index % 2 !== 0 && index - 1 >= 0) {
                      siblingIndex = index - 1;
                  }

                  if (siblingIndex !== -1) {
                      const sibling = siblings[siblingIndex];
                      if (sibling.open !== details.open) {
                          sibling.open = details.open;
                          const siblingGroup = sibling.getAttribute('data-group-name');
                          if (siblingGroup) {
                              localStorage.setItem('wk2026uitslagen_open_' + siblingGroup, sibling.open);
                          }
                      }
                  }
              }
          }
        });
      }
    });

    // Persist phase blocks state
    const phaseElements = document.querySelectorAll('details.phase-details');
    phaseElements.forEach(details => {
      const phaseName = details.getAttribute('data-phase');
      if (phaseName) {
        const key = 'wk2026uitslagen_phase_' + phaseName;
        const storedState = localStorage.getItem(key);
        if (storedState !== null) {
          if (storedState === 'true') {
            details.setAttribute('open', '');
          } else {
            details.removeAttribute('open');
          }
        }

        details.addEventListener('toggle', (e) => {
          localStorage.setItem(key, details.open);
        });
      }
    });
  });
</script>
</body>
</html>
