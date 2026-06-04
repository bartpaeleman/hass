<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Dashboard</title>
    <link rel="stylesheet" href="CSS/live-dashboard.css">
</head>
<body>
    <div class="house-wrap">
        <div class="overlay-grid">
            <div class="overlay-group">
                <h3>Zonne-energie</h3>
                <div class="overlay-item solar">
                    <span class="label">Productie:</span>
                    <span id="live-solar-prod">— W</span>
                </div>
            </div>
            <div class="overlay-group">
                <h3>Batterij</h3>
                <div class="overlay-item battery" id="live-batt-status-container">
                    <span class="label">Status:</span>
                    <span id="live-batt-status-vermogen">—</span>
                </div>
                <div class="overlay-item battery">
                    <span class="label">Beschikbaar:</span>
                    <span id="live-batt-soc">— %</span>
                </div>
            </div>
            <div class="overlay-group">
                <h3>Stroomnet</h3>
                <div class="overlay-item grid-import">
                    <span class="label">Verbruik:</span>
                    <span id="live-grid-import">— W</span>
                </div>
                <div class="overlay-item grid-export">
                    <span class="label">Injectie:</span>
                    <span id="live-grid-export">— W</span>
                </div>
            </div>
        </div>

        <div class="gas-overlay">
            <h3>Gasverbruik Vandaag</h3>
            <div class="overlay-item gas">
                <span id="live-gas-vandaag">— m³</span>
            </div>
        </div>

        <div class="electricity-overlay">
            <h3>Electriciteit Vandaag</h3>
            <div class="overlay-item electricity">
                <span id="live-electricity-vandaag">— kWh</span>
            </div>
        </div>

        <div class="injection-overlay">
            <h3>Totaal Injectie Vandaag</h3>
            <div class="overlay-item injection">
                <span id="live-injection-vandaag">— kWh</span>
            </div>
        </div>

        <img src="IMGS/MyHouse.png" alt="My House">
        <svg id="Utilities" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1797.28 1003.13">
<!-- Gas Flows (Blue) -->
  <!-- Gas-2 (leverancier naar meter): bottom to top -->
  <path id="Gas-2" class="cls-1" d="M 928.41 892.99 L 928.41 745.59" />
  <!-- Gas (meter naar ketel): right to left, then up -->
  <path id="Gas" class="cls-1" d="M 921.51 738.52 L 745.13 738.52 L 745.13 681.21" />

  <!-- Import Net (Yellow) -->
  <!-- ImportGrid (leverancier naar meter): bottom to top -->
  <path id="ImportGrid" class="cls-4" d="M 935.21 904.04 L 935.21 745.59" />
  <!-- ImportGrid-2 (meter naar huis): right to left, splits up into two terminals -->
  <!-- Splitting path into two so it can flow simultaneously -->
  <path id="ImportGrid-2" class="cls-4" d="M 921.51 728.73 L 857.21 728.73 L 857.21 718.54 M 906.87 728.73 L 906.87 718.54" />

  <!-- Batterij (Orange) -->
  <!-- BatteryUsed (batterij naar huis): top to down, left, down -->
  <path id="BatteryUsed" class="cls-2" d="M 1056.14 339.53 L 1056.14 359.90 L 918.73 359.90 L 918.73 603.30" />

  <!-- Export Net (Red) -->
  <!-- ExportGrid (omvormer naar meter): right to down, left, down -->
  <path id="ExportGrid" class="cls-3" d="M 1013.09 263.83 L 1086.96 263.83 L 1086.96 370.86 L 939.23 370.86 L 939.23 712.41" />
  <!-- ExportGrid-2 (meter naar leverancier): top to bottom -->
  <path id="ExportGrid-2" class="cls-3" d="M 942.21 745.59 L 942.21 904.04" />

  <!-- Solar (Green) -->
  <!-- SolarToBattery (zon naar batterij): top to down, right -->
  <path id="SolarToBattery" class="cls-5" d="M 994.35 286.75 L 994.35 329.01 L 1033.83 329.01" />
  <!-- SolarUsed (zon naar huis): top to down, left, down -->
  <path id="SolarUsed" class="cls-5" d="M 985.40 286.75 L 985.40 352.59 L 908.24 352.59 L 908.24 603.60" />
        </svg>
    </div>

    <script src="ha_core_js.php"></script>
    <script src="JS/live-dashboard.js"></script>
    <script>
        // Voeg klik-navigatie toe zoals gevraagd
        document.body.addEventListener('click', function() {
            window.location.href = 'energy.php';
        });
    </script>
</body>
</html>
