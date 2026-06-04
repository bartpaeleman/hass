<?php

class EnergyManager {
    public function processData(array $sensorData): array {
        // Parse raw values safely
        $solarProd = floatval($sensorData['sensor.zonneenergie_productie_nu'] ?? 0);
        $gridImport = floatval($sensorData['sensor.electriciteit_netverbruik_nu'] ?? 0);
        $gridExport = floatval($sensorData['sensor.electriciteit_injectie_nu'] ?? 0);
        $battStatus = $sensorData['sensor.batterij_status'] ?? '';
        $battPower = floatval($sensorData['sensor.batterij_vermogen'] ?? 0);
        $battSOC = floatval($sensorData['sensor.adj0b1302u_state_of_charge'] ?? 0);
        $brutoVerbruik = floatval($sensorData['sensor.actueel_bruto_elektriciteitsverbruik'] ?? 0);

        $isDischarging = (strtolower($battStatus) === 'ontladen' || strtolower($battStatus) === 'ontlagen');

        // Logical condition flags
        $solarToHomeActive = false;
        if ($solarProd > 10) {
            if ($gridImport == 0 && strtolower($battStatus) !== 'laden') {
                $solarToHomeActive = true;
            }
            // "Wanneer de zonne-energie productie ... groter is dan het vermogen dat ontladen wordt van de batterij ... dan wordt er op dat moment ook rechtstreeks energie verbruikt van de zonnepanelen"
            if ($isDischarging && $solarProd > $battPower) {
                $solarToHomeActive = true;
            }
            // "Zorg wel dat als sensor.zonneenergie_productie_nu > 10 en sensor.actueel_bruto_elektriciteitsverbruik niet kleiner is dan die waarde dat de animatie ... ook actief wordt"
            if ($brutoVerbruik >= $solarProd) {
                $solarToHomeActive = true;
            }
        }

        // Booleans for state-machine flows
        $flows = [
            'gas' => true, // Gas always active based on specs
            'solarToHome' => $solarToHomeActive,
            'solarToBattery' => ($solarProd > 10 && strtolower($battStatus) === 'laden'),
            'exportGrid' => ($gridExport > 10),
            'importGrid' => ($gridImport > 0),
            'batteryUsed' => ($isDischarging && $battSOC > 15)
        ];

        // Format metrics for display (pass-through mapping to what UI expects)
        $metrics = [];
        foreach ($sensorData as $key => $value) {
            $safeId = str_replace('.', '-', $key);
            $metrics[$safeId] = $value;
        }

        return [
            'metrics' => $metrics,
            'flows' => $flows
        ];
    }
}
