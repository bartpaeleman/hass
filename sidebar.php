<div class="sidebar-section">
  <div class="section-label">Navigatie</div>

  <?php
    $configFile = __DIR__ . '/JSON/config_data.json';
    $configData = file_exists($configFile) ? json_decode(file_get_contents($configFile), true) : ['pages' => []];
    $currentRole = $_SESSION['role_level'] ?? 10;

    // Default fallback als config_data.json leeg is
    if (empty($configData['pages'])) {
        $configData['pages'] = [
            'index.php' => ['name' => 'START', 'emoji' => '🏠', 'roles' => [99, 50, 10, 0], 'hover' => 'var(--accent)'],
            'informatie.php' => ['name' => 'INFORMATIE', 'emoji' => 'ℹ️', 'roles' => [99, 50, 10, 0], 'hover' => 'var(--accent)'],
            'automatisering.php' => ['name' => 'AUTOMATISERING', 'emoji' => '🤖', 'roles' => [99, 50, 10, 0], 'hover' => 'var(--accent)'],
            'monitoring.php' => ['name' => 'ALARM', 'emoji' => '🚨', 'roles' => [99, 50, 10, 0], 'hover' => 'var(--alert)'],
            'energy.php' => ['name' => 'ENERGIE', 'emoji' => '⚡', 'roles' => [99, 50, 10, 0], 'hover' => 'var(--ok)'],
            'verwarming.php' => ['name' => 'VERWARMING', 'emoji' => '🔥', 'roles' => [99, 50, 10, 0], 'hover' => 'var(--warn)'],
            'airco.php' => ['name' => 'AIRCO', 'emoji' => '❄️', 'roles' => [99, 50, 10, 0], 'hover' => 'var(--accent)'],
            'verlichting.php' => ['name' => 'VERLICHTING', 'emoji' => '💡', 'roles' => [99, 50, 10, 0], 'hover' => '#f9a825'],
            'kalender.php' => ['name' => 'KALENDER', 'emoji' => '🗓️', 'roles' => [99, 50, 10, 0], 'hover' => 'var(--accent)'],
            'wk2026.php' => ['name' => 'WK VOETBAL', 'emoji' => '⚽', 'roles' => [99, 50, 10, 0], 'hover' => 'var(--accent)']
        ];
    }

    // Kleuren voor specifieke items bij fallback
    $hoverColors = [
        'index.php' => 'var(--accent)',
        'informatie.php' => 'var(--accent)',
        'automatisering.php' => 'var(--accent)',
        'monitoring.php' => 'var(--alert)',
        'energy.php' => 'var(--ok)',
        'verwarming.php' => 'var(--warn)',
        'airco.php' => 'var(--accent)',
        'verlichting.php' => '#f9a825',
        'kalender.php' => 'var(--accent)',
        'wk2026.php' => 'var(--accent)'
    ];

    foreach ($configData['pages'] as $file => $page) {
        // Skip index.php als we er al op zijn
        if ($file === 'index.php' && basename($_SERVER['PHP_SELF']) === 'index.php') {
            continue;
        }

        // Controleer of role in array zit (index.php is ALL ON)
        $roles = $page['roles'] ?? [];
        if ($file !== 'index.php' && !in_array($currentRole, $roles) && $currentRole < 99) {
            continue;
        }

        $hoverColor = $hoverColors[$file] ?? 'var(--accent)';

        // Speciale logica voor Energie Dashboard
        $linkHref = $file;
        if ($file === 'energy.php') {
            $linkHref = (basename($_SERVER['PHP_SELF']) === 'energy.php') ? 'live-dashboard.php' : 'energy.php';
        }

        echo '<a href="' . htmlspecialchars($linkHref, ENT_QUOTES, 'UTF-8') . '" style="display:flex; align-items:center; gap:8px; padding:12px; margin-bottom:10px; background:var(--surface); border:1px solid var(--border); border-radius:6px; color:var(--text); text-decoration:none; font-family:\'Share Tech Mono\', monospace; font-size:14px; transition:border-color 0.3s;" onmouseover="this.style.borderColor=\'' . $hoverColor . '\'" onmouseout="this.style.borderColor=\'var(--border)\'">';
        echo '<span style="font-size:18px;">' . htmlspecialchars($page['emoji'], ENT_QUOTES, 'UTF-8') . '</span> ' . htmlspecialchars(strtoupper($page['name']), ENT_QUOTES, 'UTF-8');
        echo '</a>';
    }
  ?>

  <?php $requireAuth = isset($configData['settings']['REQUIRE_AUTH']) ? $configData['settings']['REQUIRE_AUTH'] : (defined('REQUIRE_AUTH') ? REQUIRE_AUTH : false); if ($requireAuth): ?>
  <div style="margin-top: 30px; border-top: 1px solid var(--border); padding-top: 20px;">
    <?php
      $displayUser = isset($_SESSION['authenticated_user']) ? strtoupper($_SESSION['authenticated_user']) : 'ACCOUNT';
    ?>
    <div style="font-family:'Share Tech Mono', monospace; font-size:14px; color:var(--text); text-align:center; margin-bottom:10px; letter-spacing:1px; font-weight:bold;">
      <?php echo htmlspecialchars($displayUser, ENT_QUOTES, 'UTF-8'); ?>
    </div>
    <?php if (isset($_SESSION['role_level']) && $_SESSION['role_level'] == 99): ?>
    <a href="ADMIN/settings_admin.php" style="display:flex; align-items:center; gap:8px; padding:12px; margin-bottom:15px; background:var(--surface); border:1px solid var(--border); border-radius:6px; color:var(--text); text-decoration:none; font-family:'Share Tech Mono', monospace; font-size:14px; transition:border-color 0.3s;" onmouseover="this.style.borderColor='var(--accent)'" onmouseout="this.style.borderColor='var(--border)'">
      <span style="font-size:18px;">⚙️</span> BEHEER INSTELLINGEN
    </a>
    <?php endif; ?>
    <a href="?logout=1" style="display:flex; align-items:center; gap:8px; padding:12px; background:var(--surface); border:1px solid var(--border); border-radius:6px; color:var(--text); text-decoration:none; font-family:'Share Tech Mono', monospace; font-size:14px; transition:border-color 0.3s;" onmouseover="this.style.borderColor='var(--alert)'" onmouseout="this.style.borderColor='var(--border)'">
      <span style="font-size:18px;">🔓</span> UITLOGGEN
    </a>
  </div>
  <?php endif; ?>
</div>
<script src="ha_core_js.php"></script>