// State Machine structure per flow-line as requested
const flowLogic = {
    gas: {
        isActive: (data) => !!data.flows?.gas,
        paths: ['Gas', 'Gas-2']
    },
    solarToHome: {
        isActive: (data) => !!data.flows?.solarToHome,
        paths: ['SolarUsed']
    },
    solarToBattery: {
        isActive: (data) => !!data.flows?.solarToBattery,
        paths: ['SolarToBattery']
    },
    exportGrid: {
        isActive: (data) => !!data.flows?.exportGrid,
        paths: ['ExportGrid', 'ExportGrid-2']
    },
    importGrid: {
        isActive: (data) => !!data.flows?.importGrid,
        paths: ['ImportGrid', 'ImportGrid-2']
    },
    batteryUsed: {
        isActive: (data) => !!data.flows?.batteryUsed,
        paths: ['BatteryUsed']
    }
};

// Hysteresis tracking variables to prevent impulse toggling
let previousEvaluatedFlows = {};
let appliedFlows = {};

/**
 * Updates the UI SVG elements and metric-cards using processed JSON from the backend.
 * Expects { metrics: {...}, flows: {...} }
 */
function updateDashboard(data) {
    if (!data || !data.flows || !data.metrics) return;

    // Evaluate state machine rules via the object's isActive method
    for (const flow in flowLogic) {
        const logic = flowLogic[flow];
        const currentlyActive = logic.isActive(data);

        // Ensure appliedFlows has an initial state on first run
        if (appliedFlows[flow] === undefined) {
            appliedFlows[flow] = currentlyActive;
            previousEvaluatedFlows[flow] = currentlyActive;
        }

        // Hysteresis logic: only apply state change if the evaluated state
        // is the same for TWO consecutive updates, AND it's different from the applied state.
        if (currentlyActive !== appliedFlows[flow]) {
            if (previousEvaluatedFlows[flow] === currentlyActive) {
                // State has persisted for 2 consecutive cycles, apply it.
                appliedFlows[flow] = currentlyActive;
                console.log(`UI Updated with flow state: ${flow} is ${currentlyActive ? 'ACTIVE' : 'INACTIVE'}`);
            } else {
                // First cycle of change, don't apply yet but log it
                console.log(`Hysteresis: Pending flow state change for ${flow} to ${currentlyActive ? 'ACTIVE' : 'INACTIVE'}`);
            }
        }

        // Update history
        previousEvaluatedFlows[flow] = currentlyActive;

        // Apply the resolved state to DOM
        const active = appliedFlows[flow];
        logic.paths.forEach(pathId => {
            const pathEl = document.getElementById(pathId);
            if (pathEl) {
                if (active) {
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
            metricEl.textContent = value;
        }
    }
}

const SENSORS_TO_FETCH = [
    'sensor.zonneenergie_productie_nu',
    'sensor.electriciteit_netverbruik_nu',
    'sensor.electriciteit_injectie_nu',
    'sensor.batterij_status'
];

/**
 * Fetches data from Home Assistant, passes it to the PHP backend to process,
 * and returns the JSON payload containing the flows and metrics.
 * Runs in a 5-second polling loop.
 */
async function fetchEnergyData() {
    // Respect the global pause logic from ha_core_js.php if it exists
    if (window.isRefreshPaused) return;

    try {
        let rawSensorData = {};

        // Fetch real data from HA API if haGetAll is available
        if (typeof haGetAll === 'function') {
            const results = await haGetAll(SENSORS_TO_FETCH);
            SENSORS_TO_FETCH.forEach((id, i) => {
                rawSensorData[id] = results[i]?.state || '0';
            });
        } else {
            console.warn("haGetAll function not found. Are you missing ha_core_js.php?");
            // Fallback mock data for testing visually without HA
            rawSensorData = {
                'sensor.zonneenergie_productie_nu': '0',
                'sensor.electriciteit_netverbruik_nu': '0',
                'sensor.electriciteit_injectie_nu': '0',
                'sensor.batterij_status': 'Idle'
            };
        }

        const response = await fetch('energy.php?ajax=1', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(rawSensorData)
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();

        if (!data || !data.flows || !data.metrics) {
            console.warn("Invalid or empty data received. Skipping UI update to prevent crashes.", data);
            return;
        }

        console.log("Successfully fetched energy data:", data);
        updateDashboard(data);

    } catch (error) {
        console.error("Failed to fetch energy data:", error);
    }
}

// Start the live-update loop
setInterval(fetchEnergyData, 5000);
fetchEnergyData(); // Initial call
