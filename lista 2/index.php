<?php
require_once("Huffman.php");

if ($argc < 2 || !file_exists($argv[1]))
    exit(1);
$fileName = $argv[1];
$start = microtime(true);
runHuffman($fileName, true, "out.txt");
$middleTime = microtime(true);
runHuffman("out.txt", false, "decoded.txt");
$end = microtime(true);

echo "encode time: " . $middleTime - $start . "\n";
echo "decode time: " . $end - $middleTime . "\n";

function runHuffman(string $fileName, bool $encode, string $outfile) : void
{
    $fileSize = filesize($fileName);
    $ff = fopen($fileName, "rb");
    $file = fread($ff, $fileSize);
    fclose($ff);
    $file = unpack(sprintf('C%d', $fileSize), $file);

    $symbolsCount = [];
    foreach ($file as $symbol) $symbolsCount[$symbol] = isset($symbolsCount[$symbol]) ? $symbolsCount[$symbol] + 1 : 1;

    $huffman = new Huffman();
    $result = $encode ? $huffman->encode($file) : $huffman->decode($file);
    $huffman->save($result, $encode, true, $outfile);
}


