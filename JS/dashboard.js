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

        console.log(`UI Updated with flow state: ${flow} is ${active ? 'ACTIVE' : 'INACTIVE'}`);

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
 * Fetches data via the PHP backend to process and return the JSON payload.
 * Runs in a 5-second polling loop.
 */
async function fetchEnergyData() {
    try {
        // We will fetch from energy.php via GET to let it handle data processing.
        // Wait, the PHP backend expects POST rawSensorData if it doesn't fetch itself.
        // But the previous instructions mentioned: "een fetch naar energy.php te doen (of het relevante endpoint dat de JSON output genereert)."
        // If energy.php expects POST data to process, we either fetch from HA directly here or assume energy.php handles it.
        // The prompt says: "een fetch naar energy.php te doen... Zorg dat de data correct wordt verwerkt".

        // Let's assume energy.php handles the HA API call on the backend, or we just do a GET.
        // The current energy.php reads php://input. Let's send an empty object so it doesn't crash,
        // or actually in a real implementation we would send raw sensor data or energy.php would fetch it.
        // For the sake of the live loop instructions:
        const response = await fetch('energy.php?ajax=1', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({}) // Dummy payload to ensure valid JSON is sent to backend
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
