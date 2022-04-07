<?php
require_once "LZ77.php";
ini_set('memory_limit', '16G');

if ($argc < 3 || !file_exists($argv[2]))
exit(1);

$mode = $argv[1];
$fileNameIn = $argv[2];
$fileNameOut = $argv[3];

runLZ77($fileNameIn, $mode == "encode", $fileNameOut);
runLZ77($fileNameOut, $mode != "encode", "decoded.txt");

function runLZ77(string $fileName, bool $encode, string $outfile): void
{
    $fileSize = filesize($fileName);
    $ff = fopen($fileName, "rb");
    $file = fread($ff, $fileSize);
    fclose($ff);
    $file = unpack(sprintf('C%d', $fileSize), $file);
    $file = array_map(function ($item) {return chr($item);}, $file);
    $lz77 = new LZ77();
    $start = microtime(true);
    $result = $encode ? $lz77->encode($file) : $lz77->decode($file);
    $end = microtime(true);
    file_put_contents($outfile, $result);
//    print_r("result: " . $result . "\n");
    print_r("===== " . ($encode ? "ENCODE" : "DECODE") . " =====\n");
    print_r("result length: " . strlen($result). " oryginal length: " . count($file) . "\n");
    print_r(($encode ? "encode" : "decode") . " time: " . $end - $start . "\n\n");
}