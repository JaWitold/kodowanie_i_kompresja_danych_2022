<?php

class compressor
{
    public static function bitmapToBytes(array $bitmap): string
    {
        $imploded = "";
        foreach ($bitmap as $b) {
            $imploded .= implode(array_map(function ($item) {
                return chr($item);
            }, $b));
        }
        return $imploded;
    }

    public static function bitmapToSave(array $bitmap): string
    {
        $imploded = "";
        foreach ($bitmap as $b) {
//            $imploded .= implode(array_map(function($item) {return chr($item);}, $b));
            $imploded .= implode(array_map(function ($item) {
                return "\n" . $item;
            }, $b));
        }
        return $imploded;
    }

    public static function parseBitmap(array $file, int $width, int $height): array
    {
        $bitmap = [];
        for ($i = 0; $i < $height * $width; $i++) {
            # b, g, r
            $bitmap[] = [$file[3 * $i], $file[3 * $i + 1], $file[3 * $i + 2]];
        }
        return $bitmap;
    }

    public static function differentialCoding(array $bitmap, array $quantized): array
    {
        $bitmapLen = count($bitmap);
//        if ($bitmapLen != count($quantized)) exit(1);
        $diff = [];
        for($i = 0; $i < $bitmapLen; $i++) {
            $qValue = $quantized[$i - 1] ?? [0, 0, 0];
            $color = [];
            foreach ($bitmap[$i] as $key => $value) {
                $color[] = $value - $qValue[$key];
            }
            $diff[] = $color;
        }

        return $diff;
    }

    public static function differentialDecoding(array $bitmap): array
    {
        $nBitmap = [];
        $prev = [0, 0, 0];
        foreach ($bitmap as $pixel) {
            $prev = [$pixel[0] + $prev[0], $pixel[1] + $prev[1], $pixel[2] + $prev[2]];
            $nBitmap[] = $prev;
        }
        return $nBitmap;
    }

    public static function uniformQuantization(array $bitmap, int $k): array
    {
        $quan = [];
//        $delta = 2 * (255 / pow(2, $k));
//        foreach ($bitmap as $pixel) {
//            $quan[] = array_map(function ($item) use ($delta) {
//                return round($delta * (ceil($item / $delta)));
//            }, $pixel);
//        }

        $delta = (256 / pow(2, $k));
        foreach ($bitmap as $pixel) {
            $quan[] = array_map(function ($item) use ($delta) {
                return $delta * (floor($item / $delta));
            }, $pixel);
        }
        return $quan;
    }
}