<?php
ini_set('memory_limit', '16G');

if ($argc < 3 || !file_exists($argv[2]))
    exit(3);

$p = $argv[1];
$fileNameIn = $argv[2];
$fileNameOut = $argv[3];

runNoise($p, $fileNameIn, $fileNameOut);

function runNoise(float $p, string $fileNameIn, string $fileNameOut): void
{
    $file = readTheFile($fileNameIn);
    $result = "";
    foreach($file as &$f) {
        for ($i = 0; $i < 8; $i++) {
            $f = mt_rand() / mt_getrandmax() < $p ? $f ^ (0b1 << $i) : $f;
        }
        $result .= chr($f);
    }
    file_put_contents($fileNameOut, $result);
}

function readTheFile(string $fileName) : array {
    $fileSize = filesize($fileName);
    $ff = fopen($fileName, "rb");
    $file = fread($ff, $fileSize);
    fclose($ff);
    return unpack(sprintf('C%d', $fileSize), $file);
}