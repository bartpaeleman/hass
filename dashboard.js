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

/**
 * Updates the UI SVG elements and metric-cards using processed JSON from the backend.
 * Expects { metrics: {...}, flows: {...} }
 */
function updateDashboard(data) {
    if (!data || !data.flows || !data.metrics) return;

    // Evaluate state machine rules via the object's isActive method
    for (const flow in flowLogic) {
        const logic = flowLogic[flow];
        const active = logic.isActive(data);

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

/**
 * Example fetch function sending raw sensor data (e.g., obtained via haGetAll)
 * to the PHP backend to process and return the JSON payload.
 */
async function fetchEnergyData(rawSensorData) {
    try {
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
        updateDashboard(data);
    } catch (error) {
        console.error("Failed to fetch energy data:", error);
    }
}
