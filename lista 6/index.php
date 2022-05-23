<?php
ini_set('memory_limit', '16G');

require_once "compressor.php";

if ($argc < 3 || !file_exists($argv[1]))
    exit(1);

$fileNameIn = $argv[1];
$fileNameOut = $argv[2];
$k = $argv[3] ?? 0;


runCompressor($fileNameIn, $fileNameOut, $k, true);
runCompressor($fileNameOut, $fileNameIn, $k, false);
stats($fileNameIn, "_" . basename($fileNameIn));


function runCompressor(string $fileNameIn, string $fileNameOut, int $k = 7, bool $encode = true): void
{
    if ($encode) {
        encode($fileNameIn, $fileNameOut, $k);
    } else {
        decode($fileNameIn, "_" . basename($fileNameOut));
    }
}

function encode(string $fileNameIn, string $fileNameOut, int $k = 7): void
{
    $file = readTheFile($fileNameIn);
    $width = $file[14] * 256 + $file[13];
    $height = $file[16] * 256 + $file[15];
    $bitmap = compressor::parseBitmap(array_slice($file, 18, 3 * $width * $height), $width, $height);
    $header = implode(array_map(function ($x) {return chr($x);}, array_slice($file, 0, 18)));
    $footer = implode(array_map(function ($x) {return chr($x);}, array_slice($file, 18 + 3 * $width * $height)));

    $quantized = compressor::uniformQuantization($bitmap, $k);
    $differences = compressor::differentialCoding($bitmap, $quantized);
    $result = compressor::uniformQuantization($differences, $k);

    $payload = compressor::bitmapToBytes($result);
    file_put_contents($fileNameOut, $header . $payload . $footer);

//    $decoded = compressor::differentialDecoding($result);
//
//    $payload = compressor::bitmapToBytes($decoded);
//    file_put_contents("out_d.tga", $header . $payload . $footer);

}

function decode(string $fileNameIn, string $fileNameOut): void
{
    $file = readTheFile($fileNameIn);
    $width = $file[14] * 256 + $file[13];
    $height = $file[16] * 256 + $file[15];
    $bitmap = compressor::parseBitmap(array_slice($file, 18, 3 * $width * $height), $width, $height);
    $header = implode(array_map(function ($x) {return chr($x);}, array_slice($file, 0, 18)));
    $footer = implode(array_map(function ($x) {return chr($x);}, array_slice($file, 18 + 3 * $width * $height)));

    $decoded = compressor::differentialDecoding($bitmap);

    $payload = compressor::bitmapToBytes($decoded);
    file_put_contents($fileNameOut, $header . $payload . $footer);
}

function stats(string $original, string $new) : void
{
    $bitmap = getBitmap($original);
    $decoded = getBitmap($new);

    $mse = compressor::mse($bitmap, $decoded);
    $snr = compressor::snr($bitmap, $mse);

    print_r("mse: \t\t\t\t" . $mse . "\n");
    print_r("red channel mse: \t". compressor::mse_i($bitmap, $decoded, 2) . "\n");
    print_r("green channel mse: \t". compressor::mse_i($bitmap, $decoded, 1) . "\n");
    print_r("blue channel mse: \t". compressor::mse_i($bitmap, $decoded, 0) . "\n");

    print_r("snr: \t\t\t\t". $snr . "\n");
}

function readTheFile(string $fileName) : array {
    $fileSize = filesize($fileName);
    $ff = fopen($fileName, "rb");
    $file = fread($ff, $fileSize);
    fclose($ff);
    return unpack(sprintf('C%d', $fileSize), $file);
}

function getBitmap(string $fileName) : array {
    $newFile = readTheFile($fileName);
    $newWidth = $newFile[14] * 256 + $newFile[13];
    $newHeight = $newFile[16] * 256 + $newFile[15];
    return compressor::parseBitmap(array_slice($newFile, 18, 3 * $newWidth * $newHeight), $newWidth, $newHeight);
}