<?php

class EnergyManager {
    public function processData(array $sensorData): array {
        // Parse raw values safely
        $solarProd = floatval($sensorData['sensor.zonneenergie_productie_nu'] ?? 0);
        $gridImport = floatval($sensorData['sensor.electriciteit_netverbruik_nu'] ?? 0);
        $gridExport = floatval($sensorData['sensor.electriciteit_injectie_nu'] ?? 0);
        $battStatus = $sensorData['sensor.batterij_status'] ?? '';

        // Booleans for state-machine flows
        $flows = [
            'gas' => true, // Gas always active based on specs
            'solarToHome' => ($solarProd > 0 && $gridImport == 0 && strtolower($battStatus) !== 'laden'),
            'solarToBattery' => ($solarProd > 0 && strtolower($battStatus) === 'laden'),
            'exportGrid' => ($gridExport > 0),
            'importGrid' => ($gridImport > 0),
            'batteryUsed' => (strtolower($battStatus) === 'ontladen' || strtolower($battStatus) === 'ontlagen')
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
