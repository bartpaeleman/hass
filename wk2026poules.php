<?php
require_once 'config.php';
require_once 'auth.php';

// Fetch poule data
$html = @file_get_contents("https://pouletips.nl/wk-2026/stand/");
$groups = [];

if ($html !== false) {
    $dom = new DOMDocument();
    @$dom->loadHTML($html);
    $xpath = new DOMXPath($dom);

    $h2s = $xpath->query('//h2');
    foreach ($h2s as $h2) {
        $groupName = trim($h2->textContent);
        if (strpos($groupName, 'Groep') === 0) {
            $table = $xpath->query('following::table[1]', $h2)->item(0);
            if ($table) {
                $rows = $xpath->query('.//tr', $table);
                $tableData = [];
                foreach ($rows as $row) {
                    $cols = $xpath->query('.//th | .//td', $row);
                    $rowData = [];
                    foreach ($cols as $col) {
                        $rowData[] = trim(preg_replace('/\s+/', ' ', $col->textContent));
                    }
                    $tableData[] = $rowData;
                }
                $groups[$groupName] = $tableData;
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
<title>WK VOETBAL 2026 POULES</title>
<link href="https://fonts.googleapis.com/css2?family=Share+Tech+Mono&family=Barlow+Condensed:wght@300;400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="CSS/common.css">
<link rel="stylesheet" href="CSS/wk2026.css">
<link rel="stylesheet" href="CSS/wk2026poules.css">
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
      <span>POULES STAND</span>
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

    <?php if (empty($groups)): ?>
      <p style="color: var(--warn); padding: 20px;">Kon de poule standen niet inladen. Probeer het later opnieuw.</p>
    <?php else: ?>
      <div class="poules-grid">
        <?php foreach ($groups as $groupName => $rows): ?>
          <details class="poule-details active-month" open>
            <summary class="poule-summary">
              <div class="month-title"><?php echo htmlspecialchars($groupName); ?></div>
            </summary>
            <div class="poule-content">
              <div style="overflow-x: auto;">
                <table class="poule-table">
                  <thead>
                    <?php
                    // Header row
                    if (!empty($rows) && isset($rows[0])) {
                        echo '<tr>';
                        foreach ($rows[0] as $idx => $th) {
                            $class = ($idx === 1) ? ' class="team-name"' : '';
                            echo '<th' . $class . '>' . htmlspecialchars($th) . '</th>';
                        }
                        echo '</tr>';
                    }
                    ?>
                  </thead>
                  <tbody>
                    <?php
                    // Data rows
                    for ($i = 1; $i < count($rows); $i++) {
                        $row = $rows[$i];
                        echo '<tr>';
                        foreach ($row as $idx => $td) {
                            $class = ($idx === 1) ? ' class="team-name"' : '';
                            echo '<td' . $class . '>' . htmlspecialchars($td) . '</td>';
                        }
                        echo '</tr>';
                    }
                    ?>
                  </tbody>
                </table>
              </div>
            </div>
          </details>
        <?php endforeach; ?>
      </div>
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
