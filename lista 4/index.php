<?php
require_once "JPEG_LS.php";
require_once "Entropy.php";
ini_set('memory_limit', '16G');

if ($argc < 1 || !file_exists($argv[1]))
    exit(1);

$fileNameIn = $argv[1];


runJPEG_LS($fileNameIn);

function runJPEG_LS(string $fileName): void
{
    $fileSize = filesize($fileName);
    $ff = fopen($fileName, "rb");
    $file = fread($ff, $fileSize);
    fclose($ff);
    $file = unpack(sprintf('C%d', $fileSize), $file);
//    $file = array_map(function($item) {return chr($item);}, $file);
    $width = $file[14] * 256 + $file[13];
    $height = $file[16] * 256 + $file[15];
    $jpeg_ls = new JPEG_LS();
    $file = (array)array_slice($file, 18, 3 * $width * $height);
//    $file = (array) array_slice($file, 0, count($file) - 26);
//    print_r(count($file) . " " . 3 * $width * $height);
    $bitmap = JPEG_LS::parseBitmap($file, $width, $height);

    [$r, $g, $b] = JPEG_LS::extractColors($bitmap);
    $colors = array_merge($r, $g, $b);
    $data = [$r, $g, $b, $colors];
    Entropy::printEntropy($data, "original");

    $minimal_r = PHP_INT_MAX;
    $minimal_g = PHP_INT_MAX;
    $minimal_b = PHP_INT_MAX;
    $minimal_c = PHP_INT_MAX;

    $best_r = PHP_INT_MAX;
    $best_g = PHP_INT_MAX;
    $best_b = PHP_INT_MAX;
    $best_c = PHP_INT_MAX;

    for($i = 0; $i < 8; $i++) {
        $temp = $jpeg_ls->encode($bitmap, $i);
        [$r, $g, $b] = JPEG_LS::extractColors($temp);
        $colors = array_merge($r, $g, $b);
        $data = [$r, $g, $b, $colors];

        $re = Entropy::calcEntropy($r);
        if($re < $minimal_r) {
            $minimal_r = $re;
            $best_r = $i;
        }

        $ge = Entropy::calcEntropy($g);
        if($ge < $minimal_g) {
            $minimal_g = $ge;
            $best_g = $i;
        }

        $be = Entropy::calcEntropy($b);
        if($be < $minimal_b) {
            $minimal_b = $be;
            $best_b = $i;
        }

        $ce = Entropy::calcEntropy($colors);
        if($ce < $minimal_c) {
            $minimal_c = $ce;
            $best_c = $i;
        }

//        if(Entropy::calcEntropy($g) < $minimal_g) {
//            $minimal_g = Entropy::calcEntropy($g);
//            $best_g = $i;
//        }
//
//        if(Entropy::calcEntropy($b) < $minimal_b) {
//            $minimal_b = Entropy::calcEntropy($b);
//            $best_b = $i;
//        }
//
//        if(Entropy::calcEntropy($colors) < $minimal_c) {
//            $minimal_c = Entropy::calcEntropy($colors);
//            $best_c = $i;
//        }
        Entropy::printEntropy($data, $i);
    }

    print_r("best red chanel schema " . $best_r . " at entropy of " . $minimal_r . "\n");
    print_r("best green chanel schema " . $best_g . " at entropy of " . $minimal_g . "\n");
    print_r("best blue chanel schema " . $best_b . " at entropy of " . $minimal_b . "\n");
    print_r("best schema " . $best_c . " at entropy of " . $minimal_c . "\n");
}