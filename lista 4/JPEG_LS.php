<?php

use JetBrains\PhpStorm\Pure;

class Pixel
{
    public function __construct(
        public int $r,
        public int $g,
        public int $b,
    )
    {
    }
}

class JPEG_LS
{
    public static array $predictionScheme = [];

    public function __construct()
    {
        self::$predictionScheme = [
            function ($n, $w, $nw) {return $w;},
            function ($n, $w, $nw) {return $n;},
            function ($n, $w, $nw) {return $nw;},
            function ($n, $w, $nw) {return $n + $w - $nw;},
            function ($n, $w, $nw) {return $n + ($w - $nw) / 2;},
            function ($n, $w, $nw) {return $w + ($n - $nw) / 2;},
            function ($n, $w, $nw) {return ($n + $w) / 2;},
            function ($n, $w, $nw) {return $nw >= max($w, $n) ? max($w, $n) : ($nw <= min($w, $n) ? min($w, $n) : $w + $n - $nw);},
        ];
    }

    #[Pure] public static function parseBitmap(array $input, int $width, int $height): array
    {
        $result = [];
        $row = [];
        for ($i = 0; $i < $width * $height; $i++) {
            $row[] = new Pixel($input[$i * 3 + 2], $input[$i * 3 + 1], $input[$i * 3]);
            if ($width == count($row)) {
                $result[] = $row;
                $row = [];
            }
        }
        return $result;
    }

    public static function extractColors(array $bitmap)
    {
        $r = [];
        $g = [];
        $b = [];
        foreach ($bitmap as $row) {
            foreach ($row as $pixel) {
                $r[] = $pixel->r;
                $g[] = $pixel->g;
                $b[] = $pixel->b;
            }
        }
        return [$r, $g, $b];
    }

    public function encode($bitmap, $schema)
    {
        $result = [];
        foreach ($bitmap as $i => $row) {
            $encoded_row = [];
            foreach ($row as $j => $pixel) {
                $n = $i == 0 ? new Pixel(0, 0, 0) : $bitmap[$i - 1][$j];
                $w = $j == 0 ? new Pixel(0, 0, 0) : $bitmap[$i][$j - 1];
                $nw = $i * $j == 0 ? new Pixel(0, 0, 0) : $bitmap[$i - 1][$j - 1];
//                print_r(((($pixel->r - self::$predictionScheme[$schema]($n->r, $w->r, $nw->r)) % 256) + 256) % 256 . "\n");
                $encoded_row[] = new Pixel(
                  ((($pixel->r - self::$predictionScheme[$schema]($n->r, $w->r, $nw->r)) % 256) + 256) % 256,
                  ((($pixel->g - self::$predictionScheme[$schema]($n->g, $w->g, $nw->g)) % 256) + 256) % 256,
                  ((($pixel->b - self::$predictionScheme[$schema]($n->b, $w->b, $nw->b)) % 256) + 256) % 256);

            }
            $result[] = $encoded_row;
        }
        return $result;
    }
}