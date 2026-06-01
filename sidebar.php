<div class="sidebar-section">
  <div class="section-label">Navigatie</div>
  <?php if (basename($_SERVER['PHP_SELF']) !== 'index.php'): ?>
  <a href="index.php" style="display:flex; align-items:center; gap:8px; padding:12px; margin-bottom:24px; background:var(--surface); border:1px solid var(--border); border-radius:6px; color:var(--text); text-decoration:none; font-family:'Share Tech Mono', monospace; font-size:14px; transition:border-color 0.3s;" onmouseover="this.style.borderColor='var(--accent)'" onmouseout="this.style.borderColor='var(--border)'">
    <span style="font-size:18px;">🏠</span> START
  </a>
  <?php endif; ?>
  <a href="informatie.php" style="display:flex; align-items:center; gap:8px; padding:12px; margin-bottom:10px; background:var(--surface); border:1px solid var(--border); border-radius:6px; color:var(--text); text-decoration:none; font-family:'Share Tech Mono', monospace; font-size:14px; transition:border-color 0.3s;" onmouseover="this.style.borderColor='var(--accent)'" onmouseout="this.style.borderColor='var(--border)'">
    <span style="font-size:18px;">ℹ️</span> INFORMATIE
  </a>
  <a href="monitoring.php" style="display:flex; align-items:center; gap:8px; padding:12px; margin-bottom:10px; background:var(--surface); border:1px solid var(--border); border-radius:6px; color:var(--text); text-decoration:none; font-family:'Share Tech Mono', monospace; font-size:14px; transition:border-color 0.3s;" onmouseover="this.style.borderColor='var(--alert)'" onmouseout="this.style.borderColor='var(--border)'">
    <span style="font-size:18px;">🚨</span> ALARM
  </a>
  <a href="energy.php" style="display:flex; align-items:center; gap:8px; padding:12px; margin-bottom:10px; background:var(--surface); border:1px solid var(--border); border-radius:6px; color:var(--text); text-decoration:none; font-family:'Share Tech Mono', monospace; font-size:14px; transition:border-color 0.3s;" onmouseover="this.style.borderColor='var(--ok)'" onmouseout="this.style.borderColor='var(--border)'">
    <span style="font-size:18px;">⚡</span> ENERGIE
  </a>
  <a href="verwarming.php" style="display:flex; align-items:center; gap:8px; padding:12px; margin-bottom:10px; background:var(--surface); border:1px solid var(--border); border-radius:6px; color:var(--text); text-decoration:none; font-family:'Share Tech Mono', monospace; font-size:14px; transition:border-color 0.3s;" onmouseover="this.style.borderColor='var(--warn)'" onmouseout="this.style.borderColor='var(--border)'">
    <span style="font-size:18px;">🔥</span> VERWARMING
  </a>
  <a href="airco.php" style="display:flex; align-items:center; gap:8px; padding:12px; margin-bottom:10px; background:var(--surface); border:1px solid var(--border); border-radius:6px; color:var(--text); text-decoration:none; font-family:'Share Tech Mono', monospace; font-size:14px; transition:border-color 0.3s;" onmouseover="this.style.borderColor='var(--accent)'" onmouseout="this.style.borderColor='var(--border)'">
    <span style="font-size:18px;">❄️</span> AIRCO
  </a>
  <a href="verlichting.php" style="display:flex; align-items:center; gap:8px; padding:12px; margin-bottom:10px; background:var(--surface); border:1px solid var(--border); border-radius:6px; color:var(--text); text-decoration:none; font-family:'Share Tech Mono', monospace; font-size:14px; transition:border-color 0.3s;" onmouseover="this.style.borderColor='#f9a825'" onmouseout="this.style.borderColor='var(--border)'">
    <span style="font-size:18px;">💡</span> VERLICHTING
  </a>
  <a href="speciale_dagen.php" style="display:flex; align-items:center; gap:8px; padding:12px; background:var(--surface); border:1px solid var(--border); border-radius:6px; color:var(--text); text-decoration:none; font-family:'Share Tech Mono', monospace; font-size:14px; transition:border-color 0.3s;" onmouseover="this.style.borderColor='var(--accent)'" onmouseout="this.style.borderColor='var(--border)'">
    <span style="font-size:18px;">🗓️</span> SPECIALE DAGEN
  </a>

  <?php if (defined('REQUIRE_AUTH') && REQUIRE_AUTH): ?>
  <div style="margin-top: 30px; border-top: 1px solid var(--border); padding-top: 20px;">
    <?php
      $displayUser = isset($_SESSION['authenticated_user']) ? strtoupper($_SESSION['authenticated_user']) : 'ACCOUNT';
    ?>
    <div style="font-family:'Share Tech Mono', monospace; font-size:14px; color:var(--text); text-align:center; margin-bottom:10px; letter-spacing:1px; font-weight:bold;">
      <?php echo htmlspecialchars($displayUser, ENT_QUOTES, 'UTF-8'); ?>
    </div>
    <a href="?logout=1" style="display:flex; align-items:center; gap:8px; padding:12px; background:var(--surface); border:1px solid var(--border); border-radius:6px; color:var(--text); text-decoration:none; font-family:'Share Tech Mono', monospace; font-size:14px; transition:border-color 0.3s;" onmouseover="this.style.borderColor='var(--alert)'" onmouseout="this.style.borderColor='var(--border)'">
      <span style="font-size:18px;">🔓</span> UITLOGGEN
    </a>
  </div>
  <?php endif; ?>
</div>
<script src="ha_core_js.php"></script>
