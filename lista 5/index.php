<?php
require_once "lbg.php";
ini_set('memory_limit', '16G');

if ($argc < 3 || !file_exists($argv[1]))
    exit(1);

$fileNameIn = $argv[1];
$fileNameOut = $argv[2];
$colorNumber = $argv[3];

runLGB($fileNameIn, $fileNameOut, $colorNumber);

function runLGB(string $fileNameIn, string $fileNameOut, int $colorNumber): void
{
    $fileInSize = filesize($fileNameIn);
    $ff = fopen($fileNameIn, "rb");
    $fileIn = fread($ff, $fileInSize);
    fclose($ff);
    $fileIn = unpack(sprintf('C%d', $fileInSize), $fileIn);

    $header = implode(array_map(function($x) {return chr($x);}, (array)array_slice($fileIn, 0, 18)));
    $width = $fileIn[14] * 256 + $fileIn[13];
    $height = $fileIn[16] * 256 + $fileIn[15];
    $originalBitmap = lbg::parseBitmap((array)array_slice($fileIn, 18, 3 * $width * $height), $width, $height);
    $footer = implode(array_map(function($x) {return chr($x);}, (array)array_slice($fileIn, 18 + 3 * $width * $height)));


    $codebook = lbg::generateCodebook($originalBitmap, pow(2, $colorNumber));
    $codebook = lbg::codebookFloor($codebook);
    $newBitmap = lbg::quantify($originalBitmap, $codebook);
    $payload = lbg::bitmapToBytes($newBitmap);

    $mse = lbg::mse($originalBitmap, $newBitmap);
    $snr = lbg::snr($originalBitmap, $mse);

    print_r("MSE: {$mse}\n");
    print_r("SNR: {$snr}\n");

    $newImage = $header . $payload . $footer;
    file_put_contents($fileNameOut, print_r($newImage, true));
}