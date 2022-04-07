<?php

class LZ77
{
    private array $dictBuffer = [];
    private array $codeBuffer = [];

    public function __construct()
    {
    }

    public function encode(array $file): string
    {
        $this->dictBuffer[] = array_shift($file);
        $result = $this->dictBuffer[0];
//        print_r("(0, {$this->dictBuffer[0]})\n");

        $this->codeBuffer = array_slice($file, 0, 256);
        $file = array_slice($file, 256);

        while (count($this->codeBuffer)) {
            $offset = 1;
            if (!in_array($this->codeBuffer[0], $this->dictBuffer)) {
                //add (0, chr($this->codeBuffer[0])
//                print_r("(0, {$this->codeBuffer[0]})\n");
                $result .= "0" . $this->codeBuffer[0];
            } else {
                //find the longest prefix in $this->codeBuffer
                $prefix = $this->longestPrefixInDictionary();
//                print_r("(" . $prefix[0] . ", {$prefix[1]})\n");

                $result .= chr($prefix[0]) . chr($prefix[1]);
                $offset = $prefix[1];
            }
            $this->moveBuffers($file, $offset);
        }
        return $result;
    }

    public function decode(array $file): string
    {
//        $this->dictBuffer = array_fill(0, 255, '');
        $firstChar = array_shift($file);
        $this->dictBuffer[] = $firstChar;
        $result = $firstChar;
//        print_r("(0, " . $firstChar . ")\n");

        unset($firstChar);
        $pairs = [];
        $fileSize = count($file);
        for ($i = 0; $i < $fileSize; $i += 2) {
            $j = $i + 1;
            $p = ord($file[$i]);
//            print("(" . ($p != 48 ? $p : $file[$i]) . ", " . ($p != 48 ? ord($file[$j]) : $file[$j]) . ")\n");
            $pairs[] = [($p != 48 ? $p : $file[$i]), ($p != 48 ? ord($file[$j]) : $file[$j])];

        }
//        print_r($pairs);
//        print_r($result);
//        print_r("\n");
        foreach($pairs as $pair) {
            if($pair[0] == 0) {
                $this->dictBuffer[] = strval($pair[1]);
                if (count($this->dictBuffer) > 256) {
                    array_shift($this->dictBuffer);
                }
//                print_r($pair[1]);
//                echo "\n";
                $result .= $pair[1];
//                print_r("(" . $pair[0] . ", " . $pair[1] . ")\n");

//                print_r($result);
//                print_r("\n");

            } else {
                $p = $pair[0];
//                print_r("(" . $p . ", " . $pair[1] . ")\n");

                $cp = array_slice($this->dictBuffer, -$p, $pair[1]);
                $this->dictBuffer = array_merge($this->dictBuffer, $cp);
                while (count($this->dictBuffer) > 256) {
                    array_shift($this->dictBuffer);
                }

                $result .= implode("", $cp);
//                print_r($result);
//                print_r("\n");
            }
        }
        return $result;

    }

    private function longestPrefixInDictionary(): array
    {
        $dict = implode($this->dictBuffer);
        $code = implode($this->codeBuffer);
//        print_r($dict);
//        print_r("\n");
//        print_r($code);
//        print_r("\n");

        $codeLen = strlen($code);
        $dictLen = strlen($dict);
        $bestI = 0;
        $bestJ = 0;
        for ($i = $codeLen; $i > 0; $i--) {
            $found = false;
            for($offset = 0; $offset < $dictLen; $offset++) {
                if(substr($dict, $offset, $i) == substr($code, 0, $i)) {
                    $found = true;
                    $bestI = $offset;
                    $bestJ = $i;
                }
            }
            if($found) {
                $bestI = $dictLen - $bestI;
//                print_r(substr($dict, $dictLen - $bestI, $bestJ) . "\n");

                break;
            }
        }

//        for ($i = 0; $i < $dictLen; $i++) {
////            echo $dict . "\n";
//            $end = min(strlen($dict), strlen($code));
//            $j = 0;
//            while ($j < $end and $dict[$j] == $code[$j]) $j++;
//            $dict = substr($dict, 1);
//
//            if ($bestJ < $j) {
//                $bestI = $i;
//                $bestJ = $j;
//            }
//        }



//        exit();
        return [$bestI, $bestJ];
    }

    private function moveBuffers(array &$file, int $offset)
    {
        $this->dictBuffer = array_merge($this->dictBuffer, array_slice($this->codeBuffer, 0, $offset));
        if (count($this->dictBuffer) > 255) {
            $this->dictBuffer = array_splice($this->dictBuffer, count($this->dictBuffer) - 255);
        }

        $this->codeBuffer = array_merge(array_slice($this->codeBuffer, $offset), array_slice($file, 0, $offset));
        $file = array_slice($file, $offset);
    }
}