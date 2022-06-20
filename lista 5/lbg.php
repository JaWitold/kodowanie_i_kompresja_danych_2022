<?php

class lbg
{
    public static function parseBitmap(array $bitmap, int $width, int $height): array
    {
        $result = [];
        for ($i = 0; $i < $width * $height; $i++) {
            $result[] = [$bitmap[3 * $i], $bitmap[3 * $i + 1], $bitmap[3 * $i + 2]];
        }
        return $result;
    }

    public static function quantify(array $bitmap, array $codebook): array
    {
        $newBitmap = [];
        foreach ($bitmap as $pixel) {
            $diffs = [];
            foreach ($codebook as $x) {
                $diffs[] = self::euclidSquared($pixel, $x);
            }
            $newBitmap[] = $codebook[array_search(min($diffs), $diffs)];
        }
        return $newBitmap;
    }

    public static function codebookFloor(array $codebook): array
    {
        $newCodebook = [];
        foreach ($codebook as $color) {
            $newCodebook[] = [floor($color[0]), floor($color[1]), floor($color[2])];
        }
        return $newCodebook;
    }

    public static function bitmapToBytes(array $bitmap): string
    {
        $imploded = "";
        foreach ($bitmap as $b) {
            $imploded .= implode(array_map(function($item) {return chr($item);}, $b));
        }
        return $imploded;
    }

    public static function mse(array $original, array $new): float|int
    {
        return array_sum(array_map(function($i) use ($original, $new) {return lbg::euclidSquared($original[$i], $new[$i]);}, range(0, count($original) - 1))) / count($original);
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

    public static function generateCodebook(array $data, int $sizeCodebook, float $epsilon = 0.1): array
    {
        $dataSize = count($data);

        $codebook = [];
        $c0 = self::avgVecOfVecs($data);
        $codebook[] = $c0;

        $avgDist = self::avgDistortionC0($c0, $data, $dataSize);

        while (count($codebook) < $sizeCodebook) {
            [$codebook, $avgDist] = self::splitCodebook($data, $codebook, $epsilon, $avgDist);
        }
        return $codebook;
    }

    private static function splitCodebook(array $data, array $codebook, float $epsilon, float $initialAvgDist): array
    {
        $dataSize = count($data);

        $newCodeVectors = [];
        foreach ($codebook as $c) {
            $c1 = self::newCodeVector($c, $epsilon);
            $c2 = self::newCodeVector($c, -$epsilon);
            $newCodeVectors[] = $c1;
            $newCodeVectors[] = $c2;
        }

        $codebook = $newCodeVectors;
        $lenCodebook = count($codebook);
        print_r("Splitting {$lenCodebook}\n");

        $avgDist = 0;
        $err = $epsilon + 1;
//        $numIter = 0;

        while ($err > $epsilon) {
            $closestCList = array_fill(0, $dataSize, 0);
            $vecsNearC = [];
            $vecsIdxsNearC = [];

            foreach ($data as $i => $vec) {
                $minDist = null;
                $closestCIndex = null;
                foreach ($codebook as $ic => $c) {
                    $d = self::euclidSquared($vec, $c);
                    if (empty($minDist) or $d < $minDist) {
                        $minDist = $d;
                        $closestCList[$i] = $c;
                        $closestCIndex = $ic;
                    }
                }
                $vecsNearC[$closestCIndex][] = $vec;
                $vecsIdxsNearC[$closestCIndex][] = $i;
            }

            for ($ic = 0; $ic < $lenCodebook; $ic++) {
                $vecs = $vecsNearC[$ic] ?? [];
                $numVecsNearC = count($vecs);
                if ($numVecsNearC > 0) {
                    $newC = self::avgVecOfVecs($vecs);
                    $codebook[$ic] = $newC;
                    foreach ($vecsIdxsNearC[$ic] as $i) {
                        $closestCList[$i] = $newC;
                    }
                }
            }
            $prevAvgDist = $avgDist > 0 ? $avgDist : $initialAvgDist;
            $avgDist = self::avgDistortionCList($closestCList, $data, $dataSize);

            $err = ($prevAvgDist - $avgDist) / $prevAvgDist;
//            $numIter += 1;
        }
        return [$codebook, $avgDist];
    }

    private static function avgVecOfVecs(array $vecs): array
    {
        $size = count($vecs);
        $avgVec = [0.0, 0.0, 0.0];
        foreach ($vecs as $vec) {
            foreach ($vec as $i => $x) {
                $avgVec[$i] += $x / $size;
            }
        }
        return $avgVec;
    }

    private static function newCodeVector(array $c, float $eps): array
    {
        return array_map(function ($x) use ($eps) {
            return $x * (1.0 + $eps);
        }, $c);
    }

    private static function avgDistortionC0(array $c0, array $data, int $size): float
    {
        $a = [];
        foreach ($data as $vec) {
            $a[] = self::euclidSquared($c0, $vec);
        }
        return array_reduce($a, function ($s, $d) use ($size) {
            return $s + $d / $size;
        }, 0);
    }

    private static function avgDistortionCList(array $cList, array $data, int $size): float
    {
        $a = [];
        foreach ($cList as $i => $ci) {
            $a[] = self::euclidSquared($ci, $data[$i]);
        }

        return array_reduce($a, function ($d, $s) use ($size) {
            return $s + $d / $size;
        }, 0);
    }

    private static function euclidSquared(array $a, array $b): float|int
    {
        $c = [];
        for ($i = 0; $i < count($a ?? []); $i++) {
            $c[] = [$a[$i], $b[$i]];
        }
        $sum = 0;
        foreach ($c as [$xa, $xb]) {
//            $sum += pow($xa - $xb, 2);
            $sum += abs($xa - $xb);
        }
        return $sum;
    }
}