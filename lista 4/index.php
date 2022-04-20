<?php
require_once "JPEG_LS.php";
require_once "Entropy.php";
ini_set('memory_limit', '16G');

if ($argc < 3 || !file_exists($argv[2]))
exit(1);

$mode = $argv[1];
$fileNameIn = $argv[2];
$fileNameOut = $argv[3];

runLZ77($fileNameIn, $mode == "encode", $fileNameOut);

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

    $symbolsCount = [];
    foreach ($file as $symbol) $symbolsCount[$symbol] = isset($symbolsCount[$symbol]) ? $symbolsCount[$symbol] + 1 : 1;
    ksort($symbolsCount);

    $entropy = new Entropy();
    $entropy->readData($symbolsCount);
    $entropyValue = $entropy->analyzeEntropy();

    $symbolsCount = [];
    $resultArray = str_split($result);
    foreach ($resultArray as $symbol) $symbolsCount[$symbol] = isset($symbolsCount[$symbol]) ? $symbolsCount[$symbol] + 1 : 1;
    ksort($symbolsCount);

    $resEntropy = new Entropy();
    $resEntropy->readData($symbolsCount);
    $resEntropyValue = $resEntropy->analyzeEntropy();

    print_r("===== " . ($encode ? "ENCODE" : "DECODE") . " =====\n");
    print_r(($encode ? "original file size: \t" . $fileSize . "\n" : ""));
    print_r(($encode ? "encoded file size: \t\t" . filesize($outfile) . "\n" : ""));
    print_r(($encode ? "compression ratio: \t\t" . $fileSize / filesize($outfile) . "\n" : ""));
    print_r(($encode ? "original file entropy: \t" . $entropyValue . "\n" : ""));
    print_r(($encode ? "encoded file entropy: \t" . $resEntropyValue . "\n" : ""));
    print_r(($encode ? "encode" : "decode") . " time: " . $end - $start . "\n\n");
}