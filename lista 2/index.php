<?php
require_once("Huffman.php");
require_once("Entropy.php");
ini_set('memory_limit', '16G');


if ($argc < 3 || !file_exists($argv[2]))
    exit(1);

$mode = $argv[1];
$fileNameIn = $argv[2];
$fileNameOut = $argv[3];

runHuffman($fileNameIn, $mode == "encode", $fileNameOut);

function runHuffman(string $fileName, bool $encode, string $outfile): void
{
    $fileSize = filesize($fileName);
    $ff = fopen($fileName, "rb");
    $file = fread($ff, $fileSize);
    fclose($ff);
    $file = unpack(sprintf('C%d', $fileSize), $file);

    $symbolsCount = [];
    foreach ($file as $symbol) $symbolsCount[$symbol] = isset($symbolsCount[$symbol]) ? $symbolsCount[$symbol] + 1 : 1;
    ksort($symbolsCount);

    $huffman = new Huffman();
    $start = microtime(true);
    $result = $encode ? $huffman->encode($file) : $huffman->decode($file);
    $end = microtime(true);

//    echo ($encode ? "encode" : "decode"). " time: " . $end - $start . "\n";

    $start = microtime(true);
    $huffman->save($result, $encode, ($outfile != ""), $outfile);
    $end = microtime(true);

//    echo "save time: " . $end - $start . "\n";
    $entropy = new Entropy();
    $entropy->readData($symbolsCount);
    $entropyValue = $entropy->analyzeEntropy();

    $compressionRate = filesize($fileName) / filesize($outfile);

    echo "Entropy: " . $entropyValue . "\n";
    echo "Compression rate: " . $compressionRate . "\n";
    echo "Avg bits per symbol: " . 8 / $compressionRate. "\n";
}


