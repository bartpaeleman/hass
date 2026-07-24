<?php

class Comfort {
    public static function getBaseRooms() {
        return [
            'Woonkamer'   => ['min' => 20.0, 'max' => 23.5],
            'Badkamer'    => ['min' => 21.0, 'max' => 24.0],
            'Bureau'      => ['min' => 19.5, 'max' => 23.0],
            'Slaapkamer'  => ['min' => 15.0, 'max' => 21.0],
            'Kinderkamer' => ['min' => 18.0, 'max' => 21.0],
            'Gastenkamer' => ['min' => 18.0, 'max' => 21.0],
            'Gang'        => ['min' => 19.0, 'max' => 22.0],
            'Toilet'      => ['min' => 18.0, 'max' => 22.0]
        ];
    }

    public static function getBoundaries() {
        $baseRooms = self::getBaseRooms();

        $configFile = __DIR__ . '/../JSON/config_data.json';
        $configData = file_exists($configFile) ? json_decode(file_get_contents($configFile), true) : [];
        $savedBoundaries = $configData['settings']['COMFORT_BOUNDARIES'] ?? [];

        $finalBoundaries = [];
        foreach ($baseRooms as $room => $defaults) {
            $saved = $savedBoundaries[$room] ?? [];
            $finalBoundaries[$room] = [
                'min' => isset($saved['min']) ? (float)$saved['min'] : $defaults['min'],
                'max' => isset($saved['max']) ? (float)$saved['max'] : $defaults['max']
            ];
        }

        // Aliases voor airco's (alles in de woonkamer map)
        $finalBoundaries['Living'] = $finalBoundaries['Woonkamer'];
        $finalBoundaries['Eetkamer'] = $finalBoundaries['Woonkamer'];
        $finalBoundaries['Keuken'] = $finalBoundaries['Woonkamer'];

        return $finalBoundaries;
    }
}

?>
