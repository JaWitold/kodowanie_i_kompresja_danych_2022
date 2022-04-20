<?php

class LZ77
{
    private static int $maxDictionaryBufferLength = 31;
    private static int $maxInputBufferLength = 32;

    private string $dictionaryBuffer = "";
    private string $inputBuffer = "";

    public function encode(array $input): string
    {
        $input = implode($input);
        $result = "";
        //set buffers
        $this->moveBuffers($input, self::$maxInputBufferLength);

        while (strlen($input) || strlen(($this->inputBuffer))) {
            $bufferOffset = 1;
            if (!str_contains($this->dictionaryBuffer, $this->inputBuffer[0])) {
                //add new character
                $result .= "0" . $this->inputBuffer[0];
            } else {
                //first character is in dictionary buffer
                $pair = $this->findLongestPrefix();//[i, j]
                $result .= chr($pair[0]) . chr($pair[1]);
                $bufferOffset = $pair[1];
            }
            $this->moveBuffers($input, $bufferOffset);
        }
        return substr($result, 1);
    }

    public function decode(array $file): string
    {
//      $input = implode($input);
        $firstChar = array_shift($file);
        $this->dictionaryBuffer .= $firstChar;
        $result = $firstChar;
        unset($firstChar);
        $pairs = [];
        $fileSize = count($file);
        for ($i = 0; $i < $fileSize; $i += 2) {
            $j = $i + 1;
            $p = ord($file[$i]);
            $pairs[] = [($p != 48 ? $p : $file[$i]), ($p != 48 ? ord($file[$j]) : $file[$j])];
//            print_r($pairs );
//            echo "\n";
        }
        foreach($pairs as $pair) {
            if($pair[0] == 0) {
                $this->dictionaryBuffer .= strval($pair[1]);
                if (strlen($this->dictionaryBuffer) > 256) {
                    $this->dictionaryBuffer = substr($this->dictionaryBuffer, 1);
                }
                $result .= $pair[1];
            } else {
                $p = $pair[0];
                $cp = substr($this->dictionaryBuffer, -$p, $pair[1]);
                $this->dictionaryBuffer .= $cp;
                while (strlen($this->dictionaryBuffer) > 256) {
                    $this->dictionaryBuffer = substr($this->dictionaryBuffer, 1);
                }
                $result .= $cp;
            }
        }

        return $result;
    }

    private function moveBuffers(string &$input, int $bufferOffset): void
    {
        $this->dictionaryBuffer .= substr($this->inputBuffer, 0, $bufferOffset);
        $dictionaryBufferLength = strlen($this->dictionaryBuffer);
        if ($dictionaryBufferLength > self::$maxDictionaryBufferLength) $this->dictionaryBuffer = substr($this->dictionaryBuffer, $dictionaryBufferLength - self::$maxDictionaryBufferLength);

        $this->inputBuffer = substr($this->inputBuffer, $bufferOffset) . substr($input, 0, $bufferOffset);

        $input = substr($input, $bufferOffset);
//        print_r($this->dictionaryBuffer . "|" .  $this->inputBuffer . "|" . $input  . "\n");
    }

    private function findLongestPrefix(): array
    {
        $bestLength = 0;
        $bestOffset = 0;

        for($i = 0; $i <= strlen($this->inputBuffer); $i++) {
            $prefix = substr($this->inputBuffer, 0, $i);
            if(($pos = strpos($this->dictionaryBuffer, $prefix)) !== false)  {
                $bestLength = $i;
                $bestOffset = strlen($this->dictionaryBuffer) - $pos;
                continue;
            }
            break;
        }
//        print_r($bestOffset . " " . $bestLength . "\n");
        return [$bestOffset, $bestLength];
    }

}