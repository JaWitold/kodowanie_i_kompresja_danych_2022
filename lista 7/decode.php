<?php
ini_set('memory_limit', '16G');



if ($argc < 2 || !file_exists($argv[1]))
    exit(1);

$fileNameIn = $argv[1];
$fileNameOut = $argv[2];

runDecode($fileNameIn, $fileNameOut);

function runDecode(string $fileNameIn, string $fileNameOut): void
{
    $file = readTheFile($fileNameIn);
    $result = "";
    $errors = 0;
    $len = count($file);

    for ($i = 1; $i <= $len; $i += 2) {
        $n = fromHammingCode($file[$i]);
        $m = fromHammingCode($file[$i + 1]);
        $errors += ($n === null) + ($m === null);
        $result .= chr(($n << 4) + $m);
    }
    print_r("errors: " . $errors);
    file_put_contents($fileNameOut, $result);
}


function fromHammingCode(int $param): ?int
{
    $HAMMING_TABLE = [
        0, 210, 85, 135,
        153, 75, 204, 30,
        225, 51, 180, 102,
        120, 170, 45, 255,
    ];

    foreach ($HAMMING_TABLE as $code) {
        $diffs = 0;
        for ($i = 0; $i < 8; $i++) $diffs += (getBit($param, $i) !== getBit($code, $i)) ? 1 : 0;
        if($diffs === 0 || $diffs === 1) return array_search($param, $HAMMING_TABLE);
    }
    return null;
}

function getBit($byte, $i): int
{
    return ($byte & pow(2, $i)) >> $i;
}

function readTheFile(string $fileName): array
{
    $fileSize = filesize($fileName);
    $ff = fopen($fileName, "rb");
    $file = fread($ff, $fileSize);
    fclose($ff);
    return unpack(sprintf('C%d', $fileSize), $file);
}