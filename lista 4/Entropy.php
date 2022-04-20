<?php

class Entropy
{
    private int $length = 0;
    private array $symbolDictionary = [];

    public function readData(array $symbols)
    {
        $this->symbolDictionary = $symbols;
        $this->length = array_sum($this->symbolDictionary);
    }

    public function analyzeEntropy(): float|int
    {
        $log_len = log($this->length, 2);
        $entropy_value = 0;
        foreach ($this->symbolDictionary as $value) {
            $entropy_value += $value * (-log($value,2 ) + $log_len);
        }
        return $entropy_value/ $this->length;
    }

    /**
     * @return int
     */
    public function getLength(): int
    {
        return $this->length;
    }

    public static function calcEntropy(array $a): float|int
    {
        $symbolsCount = [];
        foreach ($a as $symbol) $symbolsCount[$symbol] = isset($symbolsCount[$symbol]) ? $symbolsCount[$symbol] + 1 : 1;
        ksort($symbolsCount);
        $entropy = new Entropy();
        $entropy->readData($symbolsCount);
        return $entropy->analyzeEntropy();
    }

    public static function printEntropy(array $a, string $name = "")
    {
        print_r("Schema: " . $name . "\n");
        print_r("red channel: " .self::calcEntropy($a[0]) . "\n");
        print_r("green channel: " .self::calcEntropy($a[1]) . "\n");
        print_r("blue channel: " .self::calcEntropy($a[2]) . "\n");
        print_r("color: " .self::calcEntropy($a[3]) . "\n");
        print_r("-------------------------\n");

    }


}