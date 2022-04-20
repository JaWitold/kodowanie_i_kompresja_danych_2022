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

}