<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="nl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Verlichting</title>
<link href="https://fonts.googleapis.com/css2?family=Share+Tech+Mono&family=Barlow+Condensed:wght@300;400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="CSS/common.css">
<link rel="stylesheet" href="CSS/verlichting.css">
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
      <h1>Verlichting</h1>
      <span>HOME AUTOMATION</span>
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

    <div class="light-grid">
      <!-- AUTOLICHTEN -->
      <div class="col-12 light-section" style="background: rgba(0, 230, 118, 0.05); border-color: rgba(0, 230, 118, 0.2);">
        <div style="padding: 12px 16px; font-weight: 600; font-size: 14px; letter-spacing: 2px; text-transform: uppercase; border-bottom: 1px solid var(--border); display: flex; align-items: center; gap: 8px;">
          <span style="font-size: 16px;">💡</span> AUTOLICHTEN
        </div>
        <div class="light-section-content">
          <div class="light-card light-off" id="card-avondlichten" onclick="toggleLight('input_boolean.avondlichten')">
            <div class="light-card-info">
              <div class="light-icon" id="icon-avondlichten">💡</div>
              <div class="light-title">Avondlichten</div>
            </div>
            <div class="light-value" id="val-avondlichten">UIT</div>
          </div>

          <div class="light-row">
            <div class="light-card light-off" id="card-portaal-licht" onclick="toggleLight('input_boolean.portaal_licht')">
              <div class="light-card-info">
                <div class="light-icon" id="icon-portaal-licht">💡</div>
                <div class="light-title">Portaal</div>
              </div>
              <div class="light-value" id="val-portaal-licht">UIT</div>
            </div>
            <div class="light-card light-off" id="card-koepel-licht" onclick="toggleLight('input_boolean.koepel_licht')">
              <div class="light-card-info">
                <div class="light-icon" id="icon-koepel-licht">💡</div>
                <div class="light-title">Koepel</div>
              </div>
              <div class="light-value" id="val-koepel-licht">UIT</div>
            </div>
          </div>

          <div class="light-row">
            <div class="light-card light-off" id="card-kerstkrans-switch" onclick="toggleLight('input_boolean.kerstkrans_switch')">
              <div class="light-card-info">
                <div class="light-icon" id="icon-kerstkrans-switch">💡</div>
                <div class="light-title">Kerstkrans</div>
              </div>
              <div class="light-value" id="val-kerstkrans-switch">UIT</div>
            </div>
            <div class="light-card light-off" id="card-auto-licht-garage" onclick="toggleLight('input_boolean.auto_licht_garage_kerst')">
              <div class="light-card-info">
                <div class="light-icon" id="icon-auto-licht-garage">💡</div>
                <div class="light-title">Garage</div>
              </div>
              <div class="light-value" id="val-auto-licht-garage">UIT</div>
            </div>
          </div>

          <div class="light-row">
            <div class="light-card light-off" id="card-plantentuin-licht" onclick="toggleLight('input_boolean.plantentuin_licht')">
              <div class="light-card-info">
                <div class="light-icon" id="icon-plantentuin-licht">💡</div>
                <div class="light-title">Plantentuin</div>
              </div>
              <div class="light-value" id="val-plantentuin-licht">UIT</div>
            </div>
            <div class="light-card light-off" id="card-achtertuin-licht" onclick="toggleLight('input_boolean.achtertuin_licht')">
              <div class="light-card-info">
                <div class="light-icon" id="icon-achtertuin-licht">💡</div>
                <div class="light-title">Tuinhuis</div>
              </div>
              <div class="light-value" id="val-achtertuin-licht">UIT</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Left Column items -->
      <div class="col-6">

        <div class="light-section">
          <div style="padding: 12px 16px; font-weight: 600; font-size: 14px; letter-spacing: 2px; text-transform: uppercase; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;">
            <div style="display:flex; align-items:center; gap:8px;"><span style="font-size: 16px;">🚪</span> AANKOMST</div>
            <div id="badge-aankomst" style="cursor:pointer;" onclick="toggleLight('light.aankomst')">⚪</div>
          </div>
          <div class="light-section-content">
            <div class="light-card light-off" id="card-portaal" onclick="toggleLight('light.portaal')">
              <div class="light-card-info">
                <div class="light-icon" id="icon-portaal">💡</div>
                <div class="light-title">Portaal</div>
              </div>
              <div class="light-value" id="val-portaal">UIT</div>
            </div>
            <div class="light-card light-off" id="card-inkomhal" onclick="toggleLight('light.inkomhal')">
              <div class="light-card-info">
                <div class="light-icon" id="icon-inkomhal">💡</div>
                <div class="light-title">Inkomhal</div>
              </div>
              <div class="light-value" id="val-inkomhal">UIT</div>
            </div>
          </div>
        </div>

        <div class="light-section">
          <div style="padding: 12px 16px; font-weight: 600; font-size: 14px; letter-spacing: 2px; text-transform: uppercase; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;">
            <div style="display:flex; align-items:center; gap:8px;"><span style="font-size: 16px;">📶</span> TRAP</div>
            <div id="badge-trap" style="cursor:pointer;" onclick="toggleLight('light.trap')">⚪</div>
          </div>
          <div class="light-section-content">
            <div class="light-row">
              <div class="light-card light-off" id="card-1e-verdieping" onclick="toggleLight('light.1e_verdieping')">
                <div class="light-card-info">
                  <div class="light-icon" id="icon-1e-verdieping">💡</div>
                  <div class="light-title">1e Verd.</div>
                </div>
                <div class="light-value" id="val-1e-verdieping">UIT</div>
              </div>
              <div class="light-card light-off" id="card-2de-verdieping" onclick="toggleLight('light.2de_verdieping')">
                <div class="light-card-info">
                  <div class="light-icon" id="icon-2de-verdieping">💡</div>
                  <div class="light-title">2de Verd.</div>
                </div>
                <div class="light-value" id="val-2de-verdieping">UIT</div>
              </div>
            </div>
          </div>
        </div>

        <div class="light-section">
          <div style="padding: 12px 16px; font-weight: 600; font-size: 14px; letter-spacing: 2px; text-transform: uppercase; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;">
            <div style="display:flex; align-items:center; gap:8px;"><span style="font-size: 16px;">🏠</span> ZONES</div>
          </div>
          <div class="light-section-content">
            <div class="light-card light-off" id="card-zetels" onclick="toggleLight('light.zetels')">
              <div class="light-card-info">
                <div class="light-icon" id="icon-zetels">💡</div>
                <div class="light-title">Zetels</div>
              </div>
              <div class="light-value" id="val-zetels">UIT</div>
            </div>
            <div class="light-card light-off" id="card-tv" onclick="toggleLight('light.tv')">
              <div class="light-card-info">
                <div class="light-icon" id="icon-tv">💡</div>
                <div class="light-title">TV</div>
              </div>
              <div class="light-value" id="val-tv">UIT</div>
            </div>
            <div class="light-card light-off" id="card-tv-ambilight" onclick="toggleLight('light.tv_woonkamer_ambilight')">
              <div class="light-card-info">
                <div class="light-icon" id="icon-tv-ambilight">💡</div>
                <div class="light-title">Ambilight</div>
              </div>
              <div class="light-value" id="val-tv-ambilight">UIT</div>
            </div>
            <div class="light-card light-off" id="card-computer" onclick="toggleLight('light.computer')">
              <div class="light-card-info">
                <div class="light-icon" id="icon-computer">💡</div>
                <div class="light-title">Computer</div>
              </div>
              <div class="light-value" id="val-computer">UIT</div>
            </div>
          </div>
        </div>

        <div class="light-section">
          <div style="padding: 12px 16px; font-weight: 600; font-size: 14px; letter-spacing: 2px; text-transform: uppercase; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;">
            <div style="display:flex; align-items:center; gap:8px;"><span style="font-size: 16px;">🍽️</span> EETGEDEELTE</div>
          </div>
          <div class="light-section-content">
            <div class="light-card light-off" id="card-keuken" onclick="toggleLight('light.keuken_2')">
              <div class="light-card-info">
                <div class="light-icon" id="icon-keuken">💡</div>
                <div class="light-title">Keuken</div>
              </div>
              <div class="light-value" id="val-keuken">UIT</div>
            </div>
            <div class="light-card light-off" id="card-eetkamer" onclick="toggleLight('light.eetkamer')">
              <div class="light-card-info">
                <div class="light-icon" id="icon-eetkamer">💡</div>
                <div class="light-title">Eetkamer</div>
              </div>
              <div class="light-value" id="val-eetkamer">UIT</div>
            </div>
          </div>
        </div>

        <div class="light-section">
          <div style="padding: 12px 16px; font-weight: 600; font-size: 14px; letter-spacing: 2px; text-transform: uppercase; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;">
            <div style="display:flex; align-items:center; gap:8px;"><span style="font-size: 16px;">🛏️</span> SLAAPKAMER</div>
            <div id="badge-slaapkamer" style="cursor:pointer;" onclick="toggleLight('light.slaapkamer')">⚪</div>
          </div>
          <div class="light-section-content">
            <div class="light-card light-off" id="card-nachtlampje-bart" onclick="toggleLight('light.nachtlampje_bart')">
              <div class="light-card-info">
                <div class="light-icon" id="icon-nachtlampje-bart">💡</div>
                <div class="light-title">Nachtlampje Bart</div>
              </div>
              <div class="light-value" id="val-nachtlampje-bart">UIT</div>
            </div>
            <div class="light-card light-off" id="card-nachtlampje-linda" onclick="toggleLight('light.nachtlampje_linda')">
              <div class="light-card-info">
                <div class="light-icon" id="icon-nachtlampje-linda">💡</div>
                <div class="light-title">Nachtlampje Linda</div>
              </div>
              <div class="light-value" id="val-nachtlampje-linda">UIT</div>
            </div>
          </div>
        </div>

      </div>

      <!-- Right Column items -->
      <div class="col-6">

        <div class="light-section">
          <div style="padding: 12px 16px; font-weight: 600; font-size: 14px; letter-spacing: 2px; text-transform: uppercase; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;">
            <div style="display:flex; align-items:center; gap:8px;"><span style="font-size: 16px;">🌳</span> BUITEN</div>
            <div style="display:flex; gap: 8px;">
              <div id="badge-buiten" style="cursor:pointer;" onclick="toggleLight('light.buiten')">⚪</div>
              <div id="badge-plantentuin" style="cursor:pointer;" onclick="toggleLight('light.plantentuin')">⚪</div>
            </div>
          </div>
          <div class="light-section-content">
            <div class="light-card light-off" id="card-buiten" onclick="toggleLight('light.buiten')">
              <div class="light-card-info">
                <div class="light-icon" id="icon-buiten">💡</div>
                <div class="light-title">Buiten</div>
              </div>
              <div class="light-value" id="val-buiten">UIT</div>
            </div>
            <div class="light-card light-off" id="card-plantentuin" onclick="toggleLight('light.plantentuin')">
              <div class="light-card-info">
                <div class="light-icon" id="icon-plantentuin">💡</div>
                <div class="light-title">Plantentuin</div>
              </div>
              <div class="light-value" id="val-plantentuin">UIT</div>
            </div>
          </div>
        </div>

        <!-- Collapsible AMBILIGHT -->
        <details class="light-section light-collapsible" id="sec-ambilight">
          <summary><span style="font-size: 16px;">📺</span> AMBILIGHT <span id="badge-ambilight" style="margin-left:8px;" onclick="event.preventDefault(); toggleLight('light.ambilight')">⚪</span></summary>
          <div class="light-section-content">
            <div class="light-row">
              <div class="light-card light-off" id="card-liv-vl" onclick="toggleLight('light.living_voor_links')">
                <div class="light-card-info"><div class="light-icon">💡</div><div class="light-title">Voor L</div></div>
              </div>
              <div class="light-card light-off" id="card-liv-sv" onclick="toggleLight('light.living_strip_voor')">
                <div class="light-card-info"><div class="light-icon">💡</div><div class="light-title">Strip</div></div>
              </div>
            </div>
            <div class="light-row">
              <div class="light-card light-off" id="card-liv-vr" onclick="toggleLight('light.living_voor_rechts')">
                <div class="light-card-info"><div class="light-icon">💡</div><div class="light-title">Voor R</div></div>
              </div>
              <div class="light-card light-off" id="card-liv-al" onclick="toggleLight('light.living_achter_links')">
                <div class="light-card-info"><div class="light-icon">💡</div><div class="light-title">Achter L</div></div>
              </div>
            </div>
            <div class="light-row">
              <div class="light-card light-off" id="card-liv-ar" onclick="toggleLight('light.living_achter_rechts')">
                <div class="light-card-info"><div class="light-icon">💡</div><div class="light-title">Achter R</div></div>
              </div>
            </div>
          </div>
        </details>

        <!-- Collapsible TV -->
        <details class="light-section light-collapsible" id="sec-tv">
          <summary><span style="font-size: 16px;">📺</span> TV <span id="badge-tv" style="margin-left:8px;" onclick="event.preventDefault(); toggleLight('light.tv')">⚪</span></summary>
          <div class="light-section-content">
            <div class="light-row">
              <div class="light-card light-off" id="card-tv-l" onclick="toggleLight('light.tv_living_links')">
                <div class="light-card-info"><div class="light-icon">💡</div><div class="light-title">Links</div></div>
              </div>
              <div class="light-card light-off" id="card-tv-m" onclick="toggleLight('light.tv_living_midden')">
                <div class="light-card-info"><div class="light-icon">💡</div><div class="light-title">Midden</div></div>
              </div>
            </div>
            <div class="light-row">
              <div class="light-card light-off" id="card-tv-r" onclick="toggleLight('light.tv_living_rechts')">
                <div class="light-card-info"><div class="light-icon">💡</div><div class="light-title">Rechts</div></div>
              </div>
            </div>
          </div>
        </details>

        <!-- Collapsible BUREAU LIVING -->
        <details class="light-section light-collapsible" id="sec-bureau">
          <summary><span style="font-size: 16px;">💻</span> BUREAU LIVING <span id="badge-computer" style="margin-left:8px;" onclick="event.preventDefault(); toggleLight('light.computer')">⚪</span></summary>
          <div class="light-section-content">
            <div class="light-row">
              <div class="light-card light-off" id="card-bur-l" onclick="toggleLight('light.bureau_living_links')">
                <div class="light-card-info"><div class="light-icon">💡</div><div class="light-title">Links</div></div>
              </div>
              <div class="light-card light-off" id="card-bur-r" onclick="toggleLight('light.bureau_living_rechts')">
                <div class="light-card-info"><div class="light-icon">💡</div><div class="light-title">Rechts</div></div>
              </div>
            </div>
          </div>
        </details>

        <!-- Collapsible ZETELS -->
        <details class="light-section light-collapsible" id="sec-zetels">
          <summary><span style="font-size: 16px;">🛋️</span> ZETELS <span id="badge-zetels" style="margin-left:8px;" onclick="event.preventDefault(); toggleLight('light.zetels')">⚪</span></summary>
          <div class="light-section-content">
            <div class="light-row">
              <div class="light-card light-off" id="card-zetel-ll" onclick="toggleLight('light.zetel_linda_links')">
                <div class="light-card-info"><div class="light-icon">🛋️</div><div class="light-title">Linda L</div></div>
              </div>
              <div class="light-card light-off" id="card-zetel-lr" onclick="toggleLight('light.zetel_linda_rechts')">
                <div class="light-card-info"><div class="light-icon">🛋️</div><div class="light-title">Linda R</div></div>
              </div>
            </div>
            <div class="light-row">
              <div class="light-card light-off" id="card-zetel-bl" onclick="toggleLight('light.zetel_bart_links')">
                <div class="light-card-info"><div class="light-icon">🛋️</div><div class="light-title">Bart L</div></div>
              </div>
              <div class="light-card light-off" id="card-zetel-br" onclick="toggleLight('light.zetel_bart_rechts')">
                <div class="light-card-info"><div class="light-icon">🛋️</div><div class="light-title">Bart R</div></div>
              </div>
            </div>
            <div class="light-card light-off" id="card-leeslamp" onclick="toggleLight('light.leeslamp')">
              <div class="light-card-info"><div class="light-icon">💡</div><div class="light-title">Leeslamp</div></div>
            </div>
          </div>
        </details>

        <!-- Collapsible KERSTVERLICHTING -->
        <details class="light-section light-collapsible" id="sec-kerst">
          <summary><span style="font-size: 16px;">🎄</span> KERSTVERLICHTING <span id="badge-kerstverlichting" style="margin-left:8px;" onclick="event.preventDefault(); toggleLight('light.kerstverlichting')">⚪</span></summary>
          <div class="light-section-content">
            <div class="light-row">
              <div class="light-card light-off" id="card-kerst-gang" onclick="toggleLight('light.kerst_gang')">
                <div class="light-card-info"><div class="light-icon">🎄</div><div class="light-title">Gang</div></div>
              </div>
              <div class="light-card light-off" id="card-kerstkrans" onclick="toggleLight('light.kerstkrans')">
                <div class="light-card-info"><div class="light-icon">🎄</div><div class="light-title">Krans</div></div>
              </div>
            </div>
            <div class="light-row">
              <div class="light-card light-off" id="card-kerst-garage" onclick="toggleLight('light.on_off_plug_1')">
                <div class="light-card-info"><div class="light-icon">🎄</div><div class="light-title">Garage</div></div>
              </div>
              <div class="light-card light-off" id="card-kerstboom" onclick="toggleLight('light.kerstboom')">
                <div class="light-card-info"><div class="light-icon">🎄</div><div class="light-title">Kerstboom</div></div>
              </div>
            </div>
            <div class="light-row">
              <div class="light-card light-off" id="card-kerst-eet" onclick="toggleLight('light.kerst_eetkamer')">
                <div class="light-card-info"><div class="light-icon">🎄</div><div class="light-title">Eetkamer</div></div>
              </div>
              <div class="light-card light-off" id="card-kerst-keu" onclick="toggleLight('light.kerst_keuken')">
                <div class="light-card-info"><div class="light-icon">🎄</div><div class="light-title">Keuken</div></div>
              </div>
            </div>
            <div class="light-row">
              <div class="light-card light-off" id="card-kerst-liv" onclick="toggleLight('light.kerst_living')">
                <div class="light-card-info"><div class="light-icon">🎄</div><div class="light-title">Living</div></div>
              </div>
              <div class="light-card light-off" id="card-kerst-vaas" onclick="toggleLight('light.kerst_vaas')">
                <div class="light-card-info"><div class="light-icon">🎄</div><div class="light-title">Vaas</div></div>
              </div>
            </div>
            <div class="light-row">
              <div class="light-card light-off" id="card-kerst-plug1" onclick="toggleLight('light.hue_smart_plug_1')">
                <div class="light-card-info"><div class="light-icon">🎄</div><div class="light-title">Plug 1</div></div>
              </div>
            </div>
          </div>
        </details>

        <!-- Collapsible TERRAS -->
        <details class="light-section light-collapsible" id="sec-terras">
          <summary><span style="font-size: 16px;">🏖️</span> TERRAS</summary>
          <div class="light-section-content">
            <div class="light-row">
              <div class="light-card light-off" id="card-buiten1" onclick="toggleLight('light.buiten_1')">
                <div class="light-card-info"><div class="light-icon">💡</div><div class="light-title">Buiten 1</div></div>
              </div>
              <div class="light-card light-off" id="card-buiten2" onclick="toggleLight('light.buiten_2')">
                <div class="light-card-info"><div class="light-icon">💡</div><div class="light-title">Buiten 2</div></div>
              </div>
            </div>
            <div class="light-card light-off" id="card-ledstrip-buiten" onclick="toggleLight('light.ledstrip_buiten')">
              <div class="light-card-info"><div class="light-icon">💡</div><div class="light-title">Ledstrip</div></div>
            </div>
          </div>
        </details>

        <!-- Collapsible PLANTENTUIN -->
        <details class="light-section light-collapsible" id="sec-plantentuin">
          <summary><span style="font-size: 16px;">🌿</span> PLANTENTUIN</summary>
          <div class="light-section-content">
            <div class="light-row">
              <div class="light-card light-off" id="card-tuin-spot1" onclick="toggleLight('light.tuin_spot_1')">
                <div class="light-card-info"><div class="light-icon">💡</div><div class="light-title">Spot 1</div></div>
              </div>
              <div class="light-card light-off" id="card-tuin-spot2" onclick="toggleLight('light.tuin_spot_2')">
                <div class="light-card-info"><div class="light-icon">💡</div><div class="light-title">Spot 2</div></div>
              </div>
            </div>
            <div class="light-row">
              <div class="light-card light-off" id="card-tuin-spot3" onclick="toggleLight('light.tuin_spot_3')">
                <div class="light-card-info"><div class="light-icon">💡</div><div class="light-title">Spot 3</div></div>
              </div>
              <div class="light-card light-off" id="card-tuin-spot4" onclick="toggleLight('light.tuin_spot_4')">
                <div class="light-card-info"><div class="light-icon">💡</div><div class="light-title">Spot 4</div></div>
              </div>
            </div>
          </div>
        </details>

      </div>
    </div>
  </div>

  <div class="right">
      <?php include 'sidebar.php'; ?>
  </div>
</main>

<script>
  const REFRESH = 5000;

  function tick() {
    document.getElementById('clock').textContent =
      new Date().toLocaleTimeString('nl-BE', { hour12: false });
  }
  tick();
  setInterval(tick, 1000);

  const LIGHT_ENTITIES = [
    { id: 'input_boolean.avondlichten', cardId: 'card-avondlichten', valId: 'val-avondlichten', onClass: 'light-alert' },
    { id: 'input_boolean.portaal_licht', cardId: 'card-portaal-licht', valId: 'val-portaal-licht', onClass: 'light-warn' },
    { id: 'input_boolean.koepel_licht', cardId: 'card-koepel-licht', valId: 'val-koepel-licht', onClass: 'light-ok' },
    { id: 'input_boolean.kerstkrans_switch', cardId: 'card-kerstkrans-switch', valId: 'val-kerstkrans-switch', onClass: 'light-ok' },
    { id: 'input_boolean.auto_licht_garage_kerst', cardId: 'card-auto-licht-garage', valId: 'val-auto-licht-garage', onClass: 'light-ok' },
    { id: 'input_boolean.plantentuin_licht', cardId: 'card-plantentuin-licht', valId: 'val-plantentuin-licht', onClass: 'light-ok' },
    { id: 'input_boolean.achtertuin_licht', cardId: 'card-achtertuin-licht', valId: 'val-achtertuin-licht', onClass: 'light-ok' },

    { id: 'light.aankomst', badgeId: 'badge-aankomst' },
    { id: 'light.portaal', cardId: 'card-portaal', valId: 'val-portaal', onClass: 'light-on' },
    { id: 'light.inkomhal', cardId: 'card-inkomhal', valId: 'val-inkomhal', onClass: 'light-on' },

    { id: 'light.trap', badgeId: 'badge-trap' },
    { id: 'light.1e_verdieping', cardId: 'card-1e-verdieping', valId: 'val-1e-verdieping', onClass: 'light-on' },
    { id: 'light.2de_verdieping', cardId: 'card-2de-verdieping', valId: 'val-2de-verdieping', onClass: 'light-on' },

    { id: 'light.zetels', cardId: 'card-zetels', valId: 'val-zetels', badgeId: 'badge-zetels', onClass: 'light-on', sectionId: 'sec-zetels' },
    { id: 'light.tv', cardId: 'card-tv', valId: 'val-tv', badgeId: 'badge-tv', onClass: 'light-on', sectionId: 'sec-tv' },
    { id: 'light.tv_woonkamer_ambilight', cardId: 'card-tv-ambilight', valId: 'val-tv-ambilight', onClass: 'light-on' },
    { id: 'light.computer', cardId: 'card-computer', valId: 'val-computer', badgeId: 'badge-computer', onClass: 'light-on', sectionId: 'sec-bureau' },

    { id: 'light.keuken_2', cardId: 'card-keuken', valId: 'val-keuken', onClass: 'light-on' },
    { id: 'light.eetkamer', cardId: 'card-eetkamer', valId: 'val-eetkamer', onClass: 'light-on' },

    { id: 'light.slaapkamer', badgeId: 'badge-slaapkamer' },
    { id: 'light.nachtlampje_bart', cardId: 'card-nachtlampje-bart', valId: 'val-nachtlampje-bart', onClass: 'light-on' },
    { id: 'light.nachtlampje_linda', cardId: 'card-nachtlampje-linda', valId: 'val-nachtlampje-linda', onClass: 'light-on' },

    { id: 'light.buiten', cardId: 'card-buiten', valId: 'val-buiten', badgeId: 'badge-buiten', onClass: 'light-on', sectionId: 'sec-terras' },
    { id: 'light.plantentuin', cardId: 'card-plantentuin', valId: 'val-plantentuin', badgeId: 'badge-plantentuin', onClass: 'light-on', sectionId: 'sec-plantentuin' },

    { id: 'light.ambilight', badgeId: 'badge-ambilight', sectionId: 'sec-ambilight' },
    { id: 'light.living_voor_links', cardId: 'card-liv-vl', onClass: 'light-on' },
    { id: 'light.living_strip_voor', cardId: 'card-liv-sv', onClass: 'light-on' },
    { id: 'light.living_voor_rechts', cardId: 'card-liv-vr', onClass: 'light-on' },
    { id: 'light.living_achter_links', cardId: 'card-liv-al', onClass: 'light-on' },
    { id: 'light.living_achter_rechts', cardId: 'card-liv-ar', onClass: 'light-on' },

    { id: 'light.tv_living_links', cardId: 'card-tv-l', onClass: 'light-on' },
    { id: 'light.tv_living_midden', cardId: 'card-tv-m', onClass: 'light-on' },
    { id: 'light.tv_living_rechts', cardId: 'card-tv-r', onClass: 'light-on' },

    { id: 'light.bureau_living_links', cardId: 'card-bur-l', onClass: 'light-on' },
    { id: 'light.bureau_living_rechts', cardId: 'card-bur-r', onClass: 'light-on' },

    { id: 'light.zetel_linda_links', cardId: 'card-zetel-ll', onClass: 'light-on' },
    { id: 'light.zetel_linda_rechts', cardId: 'card-zetel-lr', onClass: 'light-on' },
    { id: 'light.zetel_bart_links', cardId: 'card-zetel-bl', onClass: 'light-on' },
    { id: 'light.zetel_bart_rechts', cardId: 'card-zetel-br', onClass: 'light-on' },
    { id: 'light.leeslamp', cardId: 'card-leeslamp', onClass: 'light-on' },

    { id: 'light.kerstverlichting', badgeId: 'badge-kerstverlichting', sectionId: 'sec-kerst' },
    { id: 'light.kerst_gang', cardId: 'card-kerst-gang', onClass: 'light-on' },
    { id: 'light.kerstkrans', cardId: 'card-kerstkrans', onClass: 'light-on' },
    { id: 'light.on_off_plug_1', cardId: 'card-kerst-garage', onClass: 'light-on' },
    { id: 'light.kerstboom', cardId: 'card-kerstboom', onClass: 'light-on' },
    { id: 'light.kerst_eetkamer', cardId: 'card-kerst-eet', onClass: 'light-on' },
    { id: 'light.kerst_keuken', cardId: 'card-kerst-keu', onClass: 'light-on' },
    { id: 'light.kerst_living', cardId: 'card-kerst-liv', onClass: 'light-on' },
    { id: 'light.kerst_vaas', cardId: 'card-kerst-vaas', onClass: 'light-on' },
    { id: 'light.hue_smart_plug_1', cardId: 'card-kerst-plug1', onClass: 'light-on' },

    { id: 'light.buiten_1', cardId: 'card-buiten1', onClass: 'light-on' },
    { id: 'light.buiten_2', cardId: 'card-buiten2', onClass: 'light-on' },
    { id: 'light.ledstrip_buiten', cardId: 'card-ledstrip-buiten', onClass: 'light-on' },

    { id: 'light.tuin_spot_1', cardId: 'card-tuin-spot1', onClass: 'light-on' },
    { id: 'light.tuin_spot_2', cardId: 'card-tuin-spot2', onClass: 'light-on' },
    { id: 'light.tuin_spot_3', cardId: 'card-tuin-spot3', onClass: 'light-on' },
    { id: 'light.tuin_spot_4', cardId: 'card-tuin-spot4', onClass: 'light-on' }
  ];

  async function toggleLight(entityId) {
    const domain = entityId.split('.')[0];
    try {
        const r = await fetch(`${HA_URL}/api/services/${domain}/toggle`, {
            method: 'POST',
            headers: {
              Authorization: `Bearer ${HA_TOKEN}`,
              'Content-Type': 'application/json'
            },
            body: JSON.stringify({ entity_id: entityId })
        });
        if (!r.ok) throw new Error(`HA Service error: ${r.status}`);
        setTimeout(refresh, 500);
    } catch (err) {
        console.error('Failed to toggle', err);
    }
  }

  async function refresh() {
    const allIds = LIGHT_ENTITIES.map(e => e.id);
    if (allIds.length > 0) {
      const results = await haGetAll(allIds);
      const stateMap = {};
      allIds.forEach((id, i) => stateMap[id] = results[i]);

      LIGHT_ENTITIES.forEach(entity => {
        const obj = stateMap[entity.id];
        const isOn = obj && obj.state === 'on';

        // Update card
        if (entity.cardId) {
          const card = document.getElementById(entity.cardId);
          const valEl = entity.valId ? document.getElementById(entity.valId) : null;

          if (card) {
             card.className = `light-card ${isOn ? 'light-on ' + (entity.onClass || '') : 'light-off'}`;
          }
          if (valEl) {
             valEl.textContent = isOn ? 'AAN' : 'UIT';
          }
        }

        // Update badge
        if (entity.badgeId) {
          const badge = document.getElementById(entity.badgeId);
          if (badge) {
            badge.textContent = isOn ? '🟡' : '⚪';
          }
        }

        // Expand/collapse logic (visibility logic from yaml)
        if (entity.sectionId && document.getElementById(entity.sectionId)) {
          // If the group state is off, collapse it (if it isn't Ambilight dependent on TV, handle specially if needed)
          // Based on yaml: visibility - condition: state entity: light.buiten state_not: "off" etc.
          const sec = document.getElementById(entity.sectionId);
          if (isOn) {
            sec.style.display = 'block';
            if(!sec.hasAttribute('open')) sec.setAttribute('open', '');
          } else {
            // Keep it in DOM, just hide it to save space when off
            sec.style.display = 'none';
          }
        }
      });

      // Ambilight visibility is based on TV state
      const tvState = stateMap['light.tv_woonkamer_ambilight'];
      const tvWoon = document.getElementById('card-tv-ambilight');
      if (tvState && tvState.state === 'unavailable') {
         if (tvWoon) tvWoon.style.display = 'none';
      }

      // Hide/Show Ambilight section based on light.tv_woonkamer_ambilight state
      const secAmbi = document.getElementById('sec-ambilight');
      if (secAmbi && tvState) {
         if (tvState.state === 'off') {
            secAmbi.style.display = 'block';
         } else {
            secAmbi.style.display = 'none'; // Because in YAML: visibility condition state light.tv_woonkamer_ambilight state: "off"
         }
      }
    }

    document.getElementById('lastRefresh').textContent =
      'Bijgewerkt: ' + new Date().toLocaleTimeString('nl-BE');
  }

  refresh();
  setInterval(() => { if (!window.isRefreshPaused) refresh(); }, REFRESH);
</script>
</body>
</html>