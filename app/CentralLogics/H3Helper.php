<?php

namespace App\CentralLogics;

class H3Helper
{
    /**
     * Approximate resolution for historical H3-like grid.
     * Higher value = smaller hexagons.
     * Resolution 8 in H3 is approx 0.46km edge length.
     * We use a degree-based approximation for simplicity.
     */
    const GRID_SIZE = 0.005;

    /**
     * Converts Latitude and Longitude to a unique Hexagon ID (String).
     * Uses flat-top axial hexagonal coordinate system.
     */
    public static function latLngToHex($lat, $lng)
    {
        $size = self::GRID_SIZE;

        // 1. Basic axial conversion
        $q = (2 / 3 * $lng) / $size;
        $r = (-1 / 3 * $lng + sqrt(3) / 3 * $lat) / $size;

        // 2. Round to nearest hex (Cube rounding)
        $x = $q;
        $z = $r;
        $y = -$x - $z;

        $rx = round($x);
        $ry = round($y);
        $rz = round($z);

        $dx = abs($rx - $x);
        $dy = abs($ry - $y);
        $dz = abs($rz - $z);

        if ($dx > $dy && $dx > $dz) {
            $rx = -$ry - $rz;
        } elseif ($dy > $dz) {
            $ry = -$rx - $rz;
        } else {
            $rz = -$rx - $ry;
        }

        // 3. Generate unique string ID
        // We use hexadecimal representation for an "H3-like" feel
        return sprintf("hex_%x_%x", (int) $rx + 1000000, (int) $rz + 1000000);
    }

    /**
     * Converts Hexagon ID back to Latitude and Longitude (Center).
     */
    public static function hexToLatLng($hex_id)
    {
        $size = self::GRID_SIZE;
        $parts = explode('_', $hex_id);
        if (count($parts) != 3)
            return null;

        $rx = (int) hexdec($parts[1]) - 1000000;
        $rz = (int) hexdec($parts[2]) - 1000000;

        // Inverse axial conversion
        $lng = 1.5 * $rx * $size;
        $lat = ($rz + 0.5 * $rx) * $size * sqrt(3);

        return ['lat' => $lat, 'lng' => $lng];
    }
}
