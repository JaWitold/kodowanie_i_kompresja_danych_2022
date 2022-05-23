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
        $delta = (256 / pow(2, $k));
        foreach ($bitmap as $pixel) {
            $quan[] = array_map(function ($item) use ($delta) {
                return $delta * (floor($item / $delta));
            }, $pixel);
        }
        return $quan;
    }

    public static function mse(array $original, array $new): float|int
    {
        return array_sum(array_map(function ($index) use ($original, $new) {
            return self::mse_i($original, $new, $index);
        }, range(0, 2)));
    }

    public static function mse_i(array $original, array $new, int $index): float|int
    {
        return array_sum(array_map(function ($item) use ($original, $new, $index) {
                return pow($original[$item][$index] - $new[$item][$index], 2);
            }, range(0, count($original) - 1))) / count($original);
    }

    public static function power(array $x): float|int
    {
        $sum = 0;
        foreach ($x as $i) {
            $sum += $i * $i;
        }
        return $sum;
    }

    public static function snr(array $x, float|int $mserr): float|int
    {
        $sum = 0;
        for ($i = 0; $i < count($x); $i++) {
            $sum += self::power($x[$i]);
        }
        return $sum / count($x) / $mserr;
    }
}