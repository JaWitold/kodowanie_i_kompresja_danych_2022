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


function runCompressor(string $fileNameIn, string $fileNameOut, int $k = 7, bool $encode = true): void
{
    if ($encode) {
        encode($fileNameIn, $fileNameOut, $k);
    } else {
        decode($fileNameIn, "_" . $fileNameOut);
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

//    $bitmap = [[2, 0, 0], [2, 0, 0], [250, 250, 250], [2, 0, 0]];

    $quantized = compressor::uniformQuantization($bitmap, $k);
    $differences = compressor::differentialCoding($bitmap, $quantized);
    $result = compressor::uniformQuantization($differences, $k);

    $payload = compressor::bitmapToBytes($result);
    file_put_contents("out.tga", $header . $payload . $footer);

    $decoded = compressor::differentialDecoding($result);

    $payload = compressor::bitmapToBytes($decoded);
    file_put_contents("out_d.tga", $header . $payload . $footer);

    print_r($result);
    print_r($decoded);

}

function decode(string $fileNameIn, string $fileNameOut): void
{

}

function readTheFile(string $fileName) : array {
    $fileSize = filesize($fileName);
    $ff = fopen($fileName, "rb");
    $file = fread($ff, $fileSize);
    fclose($ff);
    return unpack(sprintf('C%d', $fileSize), $file);
}