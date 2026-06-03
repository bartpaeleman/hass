-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
REQUIREMENT:
-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

Ik wil een LIVE dashboard maken voor energie. Momenteel mag het standalone en moet het nog niet in de sidebargeïntegreerd worden. Ik wil eerst de functionaliteit werkend krijgen.

Hiervoor gebruiken we de MyHouse.svg en de MyHouse.png beelden uit de /IMGS folder.

De SVG beschrijft de energie flows die in transpositie op de PNG afbeelding van het huis worden geplaatst, vanuit de batterij en zonnepanelen en batterij, naar de elektriciteitsmeter en apparaten onderaan. Ik wil dit in een HTML pagina gebruiken waar de PNG onderaan komt t liggen met de SVG er bovenop maar ik wil op een of andere manier de stroom aan energie in de gekleurde banen weergeven door een animatie als die plaatsvindt. Deze animatie gaat steeds in een bepaalde richting. 

De SVG bevat gekleurde polygoon en rect elementen die energie-stromen voorstellen:

Groen (cls-5): Zonne-energie → gebruik / batterij
Oranje (cls-2): Batterij → gebruik 0 #f7941d;
Geel (cls-4): Importeren van net - #fee756;
Rood (cls-3): Exporteren naar net - #ed1c24;
Blauw (cls-1): Gas - #00aeef; #39b54a;

De opbouw voor de animatie gebeurt door een stroke-dasharray + stroke-dashoffset animatie op de paden. In plaats van gevulde polygonen gebruik je de outline van het pad als een gestippelde lijn die beweegt — dit geeft het klassieke "stromende energie" effect.

De voorgestelde aanpak:

PNG als achtergrond in een <div>
SVG er bovenop met position: absolute
Elke flow krijgt een <path> equivalent met een animerende stroke-dashoffset in de richting van de stroom

  ::view-transition-group(*),
  ::view-transition-old(*),
  ::view-transition-new(*) {
    animation-duration: 0.25s;
    animation-timing-function: cubic-bezier(0.19, 1, 0.22, 1);
  }


De techniek die best gebruikt wordt:
stroke-dasharray + stroke-dashoffset animatie — elke flow is een <path> met een gestippeld patroon dat via een CSS keyframe continu verschuift. Richting stuur je via het teken van de eindwaarde: negatief = vooruit (van bron naar doel), positief = achteruit.

De paden moeten worden afgeleid van de polygoon-coördinaten in de SVG, maar handmatig vereenvoudigd tot <path d="M ... L ..."> lijnen — Dit is nodig voor de dashoffset animatie (gevulde polygonen animeren zo niet).
De snelheid per flow pas je aan via de animatieduur in CSS (animation: flow-fwd 0.9s linear infinite — lager getal = sneller).


-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
SVG CODE OVERLAY UTITLITIES:
-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

<?xml version="1.0" encoding="UTF-8"?>
<svg id="Utilities" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1797.28 1003.13">
  <defs>
    <style>
      .cls-1 {
        fill: #00aeef;
      }

      .cls-2 {
        fill: #f7941d;
      }

      .cls-3 {
        fill: #ed1c24;
      }

      .cls-4 {
        fill: #fee756;
      }

      .cls-5 {
        fill: #39b54a;
      }
    </style>
  </defs>
  <polygon id="Gas" class="cls-1" points="921.51 740.56 743.09 740.56 743.09 681.21 747.17 681.21 747.17 736.48 921.51 736.48 921.51 740.56"/>
  <rect id="Gas-2" data-name="Gas" class="cls-1" x="926.38" y="745.59" width="4.07" height="147.4"/>
  <rect id="ImportGrid" class="cls-4" x="933.17" y="745.59" width="4.07" height="158.45"/>
  <polygon id="ImportGrid-2" data-name="ImportGrid" class="cls-4" points="908.91 726.69 908.91 718.54 904.83 718.54 904.83 726.69 859.24 726.69 859.24 718.54 855.17 718.54 855.17 730.77 921.51 730.77 921.51 726.69 908.91 726.69"/>
  <polygon id="BatteryUsed" class="cls-2" points="920.76 603.3 916.69 603.3 916.69 357.92 1054.1 357.92 1054.1 339.53 1058.17 339.53 1058.17 361.87 920.76 361.87 920.76 603.3"/>
  <polygon id="ExportGrid" class="cls-3" points="941.26 712.41 937.19 712.41 937.19 368.88 1084.92 368.88 1084.92 265.81 1013.09 265.81 1013.09 261.84 1088.99 261.84 1088.99 372.84 941.26 372.84 941.26 712.41"/>
  <rect id="ExportGrid-2" data-name="ExportGrid" class="cls-3" x="940.17" y="745.59" width="4.07" height="158.45"/>
  <polygon id="SolarToBattery" class="cls-5" points="1033.83 331.32 992.35 332.76 992.35 286.75 996.34 286.75 996.34 326.69 1033.83 326.69 1033.83 331.32"/>
  <polygon id="SolarUsed" class="cls-5" points="910.28 603.6 906.2 603.6 906.2 350.61 983.36 350.61 983.36 286.75 987.43 286.75 987.43 354.56 910.28 354.56 910.28 603.6"/>
</svg>


-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
VOORBEELD CODE:
-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

html

<!DOCTYPE html>
<html lang="nl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Energiedashboard</title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }

  body {
    background: #0f1117;
    color: #e2e8f0;
    font-family: 'Segoe UI', system-ui, sans-serif;
    min-height: 100vh;
    padding: 16px;
  }

  h1 {
    font-size: 18px;
    font-weight: 500;
    color: #94a3b8;
    margin-bottom: 16px;
    letter-spacing: 0.05em;
    text-transform: uppercase;
  }

  /* ── House visual ── */
  .house-wrap {
    position: relative;
    width: 100%;
    max-width: 900px;
    margin: 0 auto 24px;
    border-radius: 12px;
    overflow: hidden;
    background: #1a1f2e;
    border: 1px solid #2d3748;
  }

  .house-wrap img {
    width: 100%;
    display: block;
    opacity: 0.92;
  }

  .house-wrap svg {
    position: absolute;
    top: 0; left: 0;
    width: 100%; height: 100%;
    pointer-events: none;
  }

  /* ── Animated flow paths ── */
  @keyframes flow-fwd { to { stroke-dashoffset: -40; } }
  @keyframes flow-rev { to { stroke-dashoffset: 40; } }

  .flow {
    fill: none;
    stroke-width: 5;
    stroke-linecap: round;
    stroke-dasharray: 12 8;
    opacity: 0;
    transition: opacity 0.4s;
  }

  .flow.active { opacity: 0.9; }
  .flow.green  { stroke: #39b54a; animation: flow-fwd 0.85s linear infinite; }
  .flow.orange { stroke: #f7941d; animation: flow-fwd 0.85s linear infinite; }
  .flow.yellow { stroke: #fee756; animation: flow-rev 1.1s linear infinite; }
  .flow.red    { stroke: #ed1c24; animation: flow-rev 0.85s linear infinite; }
  .flow.blue   { stroke: #00aeef; animation: flow-fwd 1.2s linear infinite; }

  /* ── Value labels ── */
  .label-layer {
    position: absolute;
    top: 0; left: 0;
    width: 100%; height: 100%;
    pointer-events: none;
  }

  .val-label {
    position: absolute;
    transform: translate(-50%, -50%);
    background: rgba(15,17,23,0.85);
    border: 1px solid rgba(255,255,255,0.12);
    border-radius: 6px;
    padding: 4px 8px;
    font-size: 11px;
    font-weight: 600;
    white-space: nowrap;
    line-height: 1.3;
    display: none;
    flex-direction: column;
    align-items: center;
  }

  .val-label.visible { display: flex; }
  .val-label .unit   { font-size: 9px; font-weight: 400; opacity: 0.7; }

  .val-solar   { color: #39b54a; border-color: rgba(57,181,74,.4); top: 27%; left: 55%; }
  .val-battery { color: #f7941d; border-color: rgba(247,148,29,.4); top: 40%; left: 60%; }
  .val-import  { color: #fee756; border-color: rgba(254,231,86,.4); top: 82%; left: 55%; }
  .val-export  { color: #ed1c24; border-color: rgba(237,28,36,.4); top: 65%; left: 53%; }
  .val-gas     { color: #00aeef; border-color: rgba(0,174,239,.4); top: 82%; left: 43%; }

  /* ── Metrics grid ── */
  .metrics {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
    gap: 12px;
    max-width: 900px;
    margin: 0 auto 20px;
  }

  .metric-card {
    background: #1a1f2e;
    border: 1px solid #2d3748;
    border-radius: 10px;
    padding: 14px 16px;
    display: flex;
    flex-direction: column;
    gap: 4px;
  }

  .metric-card .label {
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: #64748b;
  }

  .metric-card .value {
    font-size: 26px;
    font-weight: 600;
    letter-spacing: -0.02em;
    line-height: 1;
  }

  .metric-card .sub {
    font-size: 11px;
    color: #475569;
    margin-top: 2px;
  }

  .metric-card.green  .value { color: #39b54a; }
  .metric-card.orange .value { color: #f7941d; }
  .metric-card.yellow .value { color: #f0c040; }
  .metric-card.red    .value { color: #ed1c24; }
  .metric-card.blue   .value { color: #00aeef; }

  .dot {
    width: 8px; height: 8px;
    border-radius: 50%;
    display: inline-block;
    margin-right: 5px;
    vertical-align: middle;
  }

  /* ── Status bar ── */
  .statusbar {
    max-width: 900px;
    margin: 0 auto;
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 12px;
    color: #475569;
    flex-wrap: wrap;
  }

  .statusbar .pill {
    background: #1a1f2e;
    border: 1px solid #2d3748;
    border-radius: 20px;
    padding: 4px 12px;
  }

  .statusbar .pill.ok  { border-color: rgba(57,181,74,.5); color: #39b54a; }
  .statusbar .pill.err { border-color: rgba(237,28,36,.5);  color: #ed1c24; }

  #last-updated { margin-left: auto; }

  /* ── Config panel ── */
  .config {
    max-width: 900px;
    margin: 20px auto 0;
    background: #1a1f2e;
    border: 1px solid #2d3748;
    border-radius: 10px;
    padding: 16px;
    font-size: 13px;
  }

  .config summary {
    cursor: pointer;
    color: #64748b;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: .05em;
    user-select: none;
  }

  .config-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px 20px;
    margin-top: 14px;
  }

  .config-row { display: flex; flex-direction: column; gap: 4px; }
  .config-row label { color: #64748b; font-size: 11px; }
  .config-row input {
    background: #0f1117;
    border: 1px solid #2d3748;
    border-radius: 6px;
    color: #e2e8f0;
    padding: 6px 10px;
    font-size: 13px;
    outline: none;
    font-family: inherit;
  }
  .config-row input:focus { border-color: #4a5568; }

  .config-actions { margin-top: 14px; display: flex; gap: 10px; }

  button {
    background: #2d3748;
    border: 1px solid #4a5568;
    border-radius: 6px;
    color: #e2e8f0;
    padding: 7px 16px;
    font-size: 13px;
    cursor: pointer;
    font-family: inherit;
    transition: background .15s;
  }
  button:hover { background: #374151; }
  button.primary { background: #1e40af; border-color: #2563eb; }
  button.primary:hover { background: #1d4ed8; }
</style>
</head>
<body>

<h1>⚡ Energiedashboard</h1>

<!-- Metrics -->
<div class="metrics">
  <div class="metric-card green">
    <div class="label"><span class="dot" style="background:#39b54a"></span>Zonne-energie</div>
    <div class="value" id="m-solar">—</div>
    <div class="sub" id="m-solar-sub">Geen data</div>
  </div>
  <div class="metric-card orange">
    <div class="label"><span class="dot" style="background:#f7941d"></span>Batterij</div>
    <div class="value" id="m-battery">—</div>
    <div class="sub" id="m-battery-sub">Geen data</div>
  </div>
  <div class="metric-card yellow">
    <div class="label"><span class="dot" style="background:#fee756"></span>Import net</div>
    <div class="value" id="m-import">—</div>
    <div class="sub" id="m-import-sub">Geen data</div>
  </div>
  <div class="metric-card red">
    <div class="label"><span class="dot" style="background:#ed1c24"></span>Export net</div>
    <div class="value" id="m-export">—</div>
    <div class="sub" id="m-export-sub">Geen data</div>
  </div>
  <div class="metric-card blue">
    <div class="label"><span class="dot" style="background:#00aeef"></span>Gas</div>
    <div class="value" id="m-gas">—</div>
    <div class="sub" id="m-gas-sub">Geen data</div>
  </div>
</div>

<!-- House visual -->
<div class="house-wrap">
  <img src="MyHouse.png">
</div>
