<?php

class LZ77
{
    private string $dictBuffer = "";
    private string $codeBuffer = "";

    public function encode(array $file): string
    {
        $text = implode($file);
        $this->dictBuffer .= $text[0];
        $text = substr($text, 1);
        $result = $this->dictBuffer[0];
        $this->codeBuffer = substr($text, 0, 256);
        $text = substr($text, 256);

        while (strlen($this->codeBuffer)) {
            $offset = 1;
            if (!str_contains($this->dictBuffer, $this->codeBuffer[0])) {
                $result .= "0" . $this->codeBuffer[0];
            } else {
                $prefix = $this->longestPrefixInDictionary();
                $result .= chr($prefix[0]) . chr($prefix[1]);
                $offset = $prefix[1];
            }
            $this->moveBuffers($text, $offset);
        }
        return $result;
    }

    public function decode(array $file): string
    {
        $firstChar = array_shift($file);
        $this->dictBuffer .= $firstChar;
        $result = $firstChar;
        unset($firstChar);
        $pairs = [];
        $fileSize = count($file);
        for ($i = 0; $i < $fileSize; $i += 2) {
            $j = $i + 1;
            $p = ord($file[$i]);
            $pairs[] = [($p != 48 ? $p : $file[$i]), ($p != 48 ? ord($file[$j]) : $file[$j])];
        }
        foreach($pairs as $pair) {
            if($pair[0] == 0) {
                $this->dictBuffer .= strval($pair[1]);
                if (strlen($this->dictBuffer) > 256) {
                    $this->dictBuffer = substr($this->dictBuffer, 1);
                }
                $result .= $pair[1];
            } else {
                $p = $pair[0];
                $cp = substr($this->dictBuffer, -$p, $pair[1]);
                $this->dictBuffer .= $cp;
                while (strlen($this->dictBuffer) > 256) {
                    $this->dictBuffer = substr($this->dictBuffer, 1);
                }
                $result .= $cp;
            }
        }
        return $result;
    }

    private function longestPrefixInDictionary(): array
    {
        $dictBufferLength = strlen($this->dictBuffer);
        $codeBufferLength = strlen($this->codeBuffer);
        $bestI = 0;
        $bestJ = 0;
        for ($i = $dictBufferLength; $i > 0; $i--) {
            $found = false;
            for($offset = 0; $offset < $codeBufferLength; $offset++) {
                if(substr($this->dictBuffer, $offset, $i) == substr($this->codeBuffer, 0, $i)) {
                    $found = true;
                    $bestI = $dictBufferLength - $offset;
                    $bestJ = $i;
                }
            }
            if($found) {
                break;
            }
        }
        return [$bestI, $bestJ];
    }

    private function moveBuffers(string &$text, int $offset)
    {
        $this->dictBuffer .= substr($this->codeBuffer, 0, $offset);
        $dictBufferLength = strlen($this->dictBuffer);
        if ($dictBufferLength > 255)
            $this->dictBuffer = substr($this->dictBuffer, $dictBufferLength - 255);

        $this->codeBuffer = substr($this->codeBuffer, $offset) . substr($text, 0, $offset);
        $text = substr($text, $offset);
    }
}