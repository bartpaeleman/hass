<?php

class Comfort {
    public static function getBoundaries() {
        return [
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
    }
}

?>
