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
  <a href="verlichting.php" style="display:flex; align-items:center; gap:8px; padding:12px; background:var(--surface); border:1px solid var(--border); border-radius:6px; color:var(--text); text-decoration:none; font-family:'Share Tech Mono', monospace; font-size:14px; transition:border-color 0.3s;" onmouseover="this.style.borderColor='#f9a825'" onmouseout="this.style.borderColor='var(--border)'">
    <span style="font-size:18px;">💡</span> VERLICHTING
  </a>
</div>
<script src="ha_core_js.php"></script>
