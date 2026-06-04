<?php

class EnergyManager {
    public function processData(array $sensorData): array {
        // Parse raw values safely
        $solarProd     = floatval($sensorData['sensor.zonneenergie_productie_nu'] ?? 0);
        $gridImport    = floatval($sensorData['sensor.electriciteit_netverbruik_nu'] ?? 0);
        $gridExport    = floatval($sensorData['sensor.electriciteit_injectie_nu'] ?? 0);
        $battStatus    = strtolower(trim($sensorData['sensor.batterij_status'] ?? ''));
        $battPower     = floatval($sensorData['sensor.batterij_vermogen'] ?? 0);
        $battSOC       = floatval($sensorData['sensor.adj0b1302u_state_of_charge'] ?? 0);
        $brutoVerbruik = floatval($sensorData['sensor.actueel_bruto_elektriciteitsverbruik'] ?? 0);
        $huisVerbruik  = floatval($sensorData['sensor.huisverbruik_totaal'] ?? 0);

        // Normalize battery status: account for typo variant 'ontlagen'
        $isCharging    = ($battStatus === 'laden');
        $isDischarging = ($battStatus === 'ontladen' || $battStatus === 'ontlagen');

        // solarToHome: actief zodra de panelen iets leveren én er tegelijk huisverbruik is.
        // Dit dekt alle subscenario's:
        //   - huis < zon  → overschot gaat naar batterij/net, maar zonnestroom wordt ook direct verbruikt
        //   - huis > zon  → tekort wordt aangevuld via batterij/net, maar zonnestroom wordt WEL verbruikt
        //   - huis = zon  → volledig zelfvoorzienend op dat moment
        // Gebruik brutoVerbruik als primaire bron; huisVerbruik als fallback.
        $effectiefVerbruik = ($brutoVerbruik > 0) ? $brutoVerbruik : $huisVerbruik;
        $solarToHome = ($solarProd > 10 && $effectiefVerbruik > 0);

        $flows = [
            // Gas is altijd actief (conform oorspronkelijke specificatie)
            'gas'            => true,

            // Zonne-energie direct naar huis
            'solarToHome'    => $solarToHome,

            // Zonne-energie naar batterij: panelen leveren, batterij laadt, en SOC is niet vol
            'solarToBattery' => ($solarProd > 10 && $isCharging && $battSOC < 100),

            // Injectie naar net: drempel 10W om ruis te vermijden
            'exportGrid'     => ($gridExport > 10),

            // Import van net: drempel 10W, consistent met andere checks
            'importGrid'     => ($gridImport > 10),

            // Batterij ontlaadt naar huis: SOC boven minimumdrempel
            'batteryUsed'    => ($isDischarging && $battSOC > 15),
        ];

        // Bouw metrics op: sensor ID's omgezet naar safe DOM-ID's (punt → koppelteken)
        $metrics = [];
        foreach ($sensorData as $key => $value) {
            $safeId = str_replace('.', '-', $key);
            $metrics[$safeId] = $value;
        }

        return [
            'metrics' => $metrics,
            'flows'   => $flows,
        ];
    }
}
