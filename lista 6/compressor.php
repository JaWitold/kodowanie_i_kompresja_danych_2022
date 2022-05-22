<?php

class compressor
{
    public static function bitmapToBytes(array $bitmap): string
    {
        $imploded = "";
        foreach ($bitmap as $b) {
            $imploded .= implode(array_map(function($item) {return chr($item);}, $b));
//            $imploded .= implode(array_map(function($item) {return "\n" . $item ;}, $b));
        }
        return $imploded;
    }

    public static function bitmapToSave(array $bitmap): string
    {
        $imploded = "";
        foreach ($bitmap as $b) {
//            $imploded .= implode(array_map(function($item) {return chr($item);}, $b));
            $imploded .= implode(array_map(function($item) {return "\n" . $item ;}, $b));
        }
        return $imploded;
    }

    public static function parseBitmap(array $file, int $width, int $height): array
    {
        $bitmap = [];
        for ($i = 0; $i < $height * $width; $i++) {
//            b,g,r
            $bitmap[] = [$file[3 * $i], $file[3 * $i + 1], $file[3 * $i + 2]];
        }
        return $bitmap;
    }

    public static function differentialCoding(array $bitmap, array $quantized): array
    {
        $diff = [];
        $len = count($bitmap);
        for($i = 0; $i < $len; $i++) {
            $quan = $quantized[$i - 1] ?? [0, 0, 0];
            $diff[$i] = [$bitmap[$i][0] - $quan[0], $bitmap[$i][1] - $quan[1], $bitmap[$i][2] - $quan[2]];
        }
        return $diff;
    }

    public static function differentialDecoding(array $bitmap): array
    {
        $diff = [];
        $prev = [0, 0, 0];
//        print_r($bitmap[0]);
//        print_r($bitmap[1]);
//        print_r($bitmap[2]);
//        print_r($bitmap[3]);
        foreach ($bitmap as $item) {
            $diff[] = [$item[0] - $prev[0], $item[1] - $prev[1], $item[2] - $prev[2]];
            $prev = $item;
        }

        foreach($diff as &$item) {
            $item = [$item[0] + 128, $item[1] + 128, $item[2] + 128];
        }
        return $diff;
    }

    public static function uniformQuantization(array $diffs, int $k): array
    {
        $quan = [];
        $delta = 256 / pow(2, $k);
        foreach ($diffs as $d) {
            $quan[] = array_map(function ($item) use ($delta) {
//                return floor($item / $delta) * $delta + ($delta / 2);
                return $delta * (floor($item / $delta) + 0.5);
            }, $d);
        }
        return $quan;
    }
}