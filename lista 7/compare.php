<?php
ini_set('memory_limit', '16G');

if ($argc < 2 || !file_exists($argv[1]) | !file_exists($argv[2]))
    exit(1);

$fileName1 = $argv[1];
$fileName2 = $argv[2];

runCheck($fileName1, $fileName2);

function runCheck(string $fileName1, string $fileName2): void
{
    $file1 = readTheFile($fileName1);
    $file2 = readTheFile($fileName2);

    if(count($file1) != count($file2)) {
        print_r("Pliki są różnych rozmiarów");
        exit(1);
    }
    $counter = 0;
    for($i = 1; $i <= count($file1); $i++) {
        if(($file1[$i] & 0b1111) != ($file2[$i] & 0b1111)) $counter += 1;
        if(($file1[$i] & 0b11110000) != ($file2[$i] & 0b11110000)) $counter += 1;
    }

    print_r($counter);
}

function readTheFile(string $fileName) : array {
    $fileSize = filesize($fileName);
    $ff = fopen($fileName, "rb");
    $file = fread($ff, $fileSize);
    fclose($ff);
    return unpack(sprintf('C%d', $fileSize), $file);
}