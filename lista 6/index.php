<?php
ini_set('memory_limit', '16G');

require_once "compressor.php";

if ($argc < 3 || !file_exists($argv[1]))
    exit(1);

$fileNameIn = $argv[1];
$fileNameOut = $argv[2];
$k = $argv[3] ?? 0;


runCompressor($fileNameIn, $fileNameOut, $k, true);
runCompressor($fileNameOut, "decompressed_" . $fileNameOut, $k, false);


function runCompressor(string $fileNameIn, string $fileNameOut, int $k = 7, bool $encode = true): void
{
    if ($encode) {
        encode($fileNameIn, $fileNameOut, $k);
    } else {
        decode($fileNameIn, $fileNameOut);
    }
}

function encode(string $fileNameIn, string $fileNameOut, int $k = 7): void
{
    $fileSize = filesize($fileNameIn);
    $ff = fopen($fileNameIn, "rb");
    $file = fread($ff, $fileSize);
    fclose($ff);
    $file = unpack(sprintf('C%d', $fileSize), $file);
//    $file = array_map(function($item) {return chr($item);}, $file);
    $width = $file[14] * 256 + $file[13];
    $height = $file[16] * 256 + $file[15];
    $bitmap = compressor::parseBitmap((array)array_slice($file, 18, 3 * $width * $height), $width, $height);
    $header = implode(array_map(function ($x) {return chr($x);}, (array)array_slice($file, 0, 18)));
    $footer = implode(array_map(function ($x) {return chr($x);}, (array)array_slice($file, 18 + 3 * $width * $height)));

//    $bitmap = [[1, 1, 1], [1, 1, 1], [255, 255, 255], [255, 255, 255]];

    $quantized = compressor::uniformQuantization($bitmap, $k);
    $diffs = compressor::differentialCoding($bitmap, $quantized);
    $_diffs = compressor::uniformQuantization($diffs, $k);
    $payload = compressor::bitmapToSave($_diffs);

    file_put_contents("header_".$fileNameOut, print_r($header, true));
    file_put_contents("footer_".$fileNameOut, print_r($footer, true));
    file_put_contents($fileNameOut, print_r(trim($payload), true));
}

function decode(string $fileNameIn, string $fileNameOut): void
{
    $fileSize = filesize("header_" . $fileNameIn);
    $ff = fopen("header_" . $fileNameIn, "rb");
    $file = fread($ff, $fileSize);
    fclose($ff);
    $file = unpack(sprintf('C%d', $fileSize), $file);
//    $file = array_map(function($item) {return chr($item);}, $file);
    $width = $file[14] * 256 + $file[13];
    $height = $file[16] * 256 + $file[15];
    $header = file_get_contents("header_" . $fileNameIn);
    $footer = file_get_contents("footer_" . $fileNameIn);

    $body = explode("\n", file_get_contents($fileNameIn));

    $bitmap = compressor::parseBitmap($body, $width, $height);
    $diffs = compressor::differentialDecoding($bitmap);

    print_r($diffs);

    $payload = compressor::bitmapToBytes($diffs);
    file_put_contents($fileNameOut, print_r($header . $payload . $footer, true));
}