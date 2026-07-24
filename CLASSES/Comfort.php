<?php

class Comfort {
    public static function getBoundaries() {
        $defaultBoundaries = [
            'Woonkamer'   => ['min' => 20.0, 'max' => 23.5],
            'Badkamer'    => ['min' => 21.0, 'max' => 24.0],
            'Bureau'      => ['min' => 19.5, 'max' => 23.0],
            'Slaapkamer'  => ['min' => 15.0, 'max' => 21.0],
            'Kinderkamer' => ['min' => 18.0, 'max' => 21.0],
            'Gastenkamer' => ['min' => 18.0, 'max' => 21.0],
            'Gang'        => ['min' => 19.0, 'max' => 22.0],
            'Toilet'      => ['min' => 18.0, 'max' => 22.0],
            // Aircos map
            'Living'      => ['min' => 20.0, 'max' => 23.5],
            'Eetkamer'    => ['min' => 20.0, 'max' => 23.5]
        ];

        $configFile = __DIR__ . '/../JSON/config_data.json';
        $configData = file_exists($configFile) ? json_decode(file_get_contents($configFile), true) : [];
        $savedBoundaries = $configData['settings']['COMFORT_BOUNDARIES'] ?? [];

        $finalBoundaries = [];
        foreach ($defaultBoundaries as $room => $defaults) {
            $saved = $savedBoundaries[$room] ?? [];
            $finalBoundaries[$room] = [
                'min' => isset($saved['min']) ? (float)$saved['min'] : $defaults['min'],
                'max' => isset($saved['max']) ? (float)$saved['max'] : $defaults['max'],
                'msg_koud' => $saved['msg_koud'] ?? 'Te koud',
                'msg_warm' => $saved['msg_warm'] ?? 'Te warm',
                'msg_ok' => $saved['msg_ok'] ?? 'Prima'
            ];
        }

        return $finalBoundaries;
    }
}

?>
