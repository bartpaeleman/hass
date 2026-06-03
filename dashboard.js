// State Machine mapping between backend JSON flow keys and SVG path IDs
const DOM_PATHS = {
    gas: ['Gas', 'Gas-2'],
    solarToHome: ['SolarUsed'],
    solarToBattery: ['SolarToBattery'],
    exportGrid: ['ExportGrid', 'ExportGrid-2'],
    importGrid: ['ImportGrid', 'ImportGrid-2'],
    batteryUsed: ['BatteryUsed']
};

/**
 * Fetches processed JSON data from energy.php via an AJAX parameter
 */
async function fetchEnergyData() {
    try {
        const response = await fetch('energy.php?ajax=1');
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const data = await response.json();
        updateDashboard(data);
    } catch (error) {
        console.error("Failed to fetch energy data:", error);
    }
}

/**
 * Updates the UI SVG elements and metric-cards using processed data from EnergyManager
 */
function updateDashboard(data) {
    if (!data || !data.flows || !data.metrics) return;

    // Evaluate state machine rules (now calculated by the backend) and toggle CSS classes
    for (const flowKey in DOM_PATHS) {
        const isActive = !!data.flows[flowKey];
        const pathIds = DOM_PATHS[flowKey];

        pathIds.forEach(pathId => {
            const pathEl = document.getElementById(pathId);
            if (pathEl) {
                if (isActive) {
                    pathEl.classList.add('active');
                } else {
                    pathEl.classList.remove('active');
                }
            }
        });
    }

    // Update metric-cards based on the safe IDs from the metrics object
    for (const [safeId, value] of Object.entries(data.metrics)) {
        const metricEl = document.getElementById(safeId);
        if (metricEl) {
            // Keep simple assignment or use specific formatting if necessary
            metricEl.textContent = value;
        }
    }
}

// Example initializing hook if used standalone or integrated in the page
// setInterval(fetchEnergyData, 3000); // Polling every 3s
