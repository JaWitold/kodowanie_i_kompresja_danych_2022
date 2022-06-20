<?php
ini_set('memory_limit', '16G');

if ($argc < 2 || !file_exists($argv[1]))
    exit(1);

$fileNameIn = $argv[1];
$fileNameOut = $argv[2];

runEncode($fileNameIn, $fileNameOut);

function runEncode(string $fileNameIn, string $fileNameOut): void
{
    $file = readTheFile($fileNameIn);
    $result = "";

    $HAMMING_TABLE = [
        chr(0), chr(210), chr(85), chr(135),
        chr(153), chr(75), chr(204), chr(30),
        chr(225), chr(51), chr(180), chr(102),
        chr(120), chr(170), chr(45), chr(255),
    ];

    foreach ($file as $byte) {
        $h_byte = ($byte & 0b11110000) >> 4;
        $l_byte = $byte & 0b1111;
        $result .= $HAMMING_TABLE[$h_byte];
        $result .= $HAMMING_TABLE[$l_byte];
    }

    file_put_contents($fileNameOut, $result);
}


function readTheFile(string $fileName): array
{
    $fileSize = filesize($fileName);
    $ff = fopen($fileName, "rb");
    $file = fread($ff, $fileSize);
    fclose($ff);
    return unpack(sprintf('C%d', $fileSize), $file);
}