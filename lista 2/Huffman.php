<?php

class Node
{
    public int $key;

    public function __construct(
        public string $symbol = '',
        public int    $weight = 0,
        public ?Node  $parent = null,
        public ?Node  $left = null,
        public ?Node  $right = null,
    )
    {
    }
}

class Huffman
{
    public Node $nyt;
    public Node $root;
    public array $nodes = [];
    public array $seen = [];

    public function __construct()
    {
        $this->nyt = new Node('NYT');
        $this->root = $this->nyt;
    }

    public function decode(array $text): string
    {
//        print_r($text);
        //remove padding
        $padding = array_shift($text);
        foreach ($text as &$k) {
            $k = str_pad(base_convert($k, 10, 2), 8, "0", STR_PAD_LEFT);
        }
        $bitText = implode("", $text);
        $bitText = substr($bitText, 0, strlen($bitText) - $padding);

        $symbol = base_convert(substr($bitText, 0, 8), 2, 10);
        $result = chr($symbol);
        $this->insert($symbol);

        $node = $this->root;
        $i = 8;
        while ($i < strlen($bitText)) {
            $node = $bitText[$i] === '0' ? $node->left : $node->right;
            $symbol = $node->symbol;

            if ($symbol != '') {
                if ($symbol === 'NYT') {
                    $symbol = base_convert(substr($bitText, $i + 1, 8), 2, 10);
                    $i += 8;
                }
                $result .= chr($symbol);
                $this->insert($symbol);
                $node = $this->root;

            }
            $i++;
        }
        return $result;
    }

    public function encode(array $text): string
    {
        $result = "";
        foreach ($text as $character) {
//            echo chr($character);
            if (isset($this->seen[$character])) {
                //node for this character already exists
                $result .= $this->getCode($character);
            } else {
                //new character
                $result .= $this->getCode('NYT');
                $result .= str_pad(base_convert($character, 10, 2), 8, '0', STR_PAD_LEFT);
            }
            $this->insert($character);
        }
        return $result;
    }

    public function save(string $bitText, bool $binaryForm = true, bool $saveTofile = true, string $out = "out.txt") : void
    {
        $text = '';

        if($binaryForm) {
            $padding = 8 - (strlen($bitText) % 8);
            $result = str_pad(base_convert($padding, 10, 2), 8, '0', STR_PAD_LEFT) . $bitText;
            $result = str_pad($result, strlen($result) + $padding, '0', STR_PAD_RIGHT);
            $len = strlen($result);
            for ($i = 0; $i < $len; $i += 8) {
                $text .= chr(base_convert(substr($result, 0, 8), 2, 10));
                $result = substr($result, 8);
            }
        } else {
            $text = $bitText;
        }

        if ($saveTofile) {
            //add padding
            file_put_contents($out, print_r($text, true));
        } else {
            print_r($text);
        }
    }

    private function getCode(string $character, Node $node = null, string $code = ""): string
    {
        $node = $node ?? $this->root;
        if ($node->left === null and $node->right === null) {
            return $node->symbol == $character ? $code : "";
        } else {
            $temporary = '';
            if ($node->left !== null) $temporary = $this->getCode($character, $node->left, $code . '0');
            if ($temporary === '' and $node->right) $temporary = $this->getCode($character, $node->right, $code . '1');
            return $temporary;
        }
    }

    private function insert(string $character)
    {
        $node = $this->seen[$character] ?? false;

        if (!$node) {
            //add new node for character
            $leaf = new Node($character, 1);
            $internalNode = new Node("", 1, $this->nyt->parent, $this->nyt, $leaf);

            $leaf->parent = $internalNode;
            $this->nyt->parent = $internalNode;

            if ($internalNode->parent !== null) {
                $internalNode->parent->left = $internalNode;
            } else {
                $this->root = $internalNode;
            }

            $this->nodes[] = $internalNode;
            $index = count($this->nodes) - 1;
            $this->nodes[$index]->key = $index;
//            print_r($this->nodes[$index]->key . " " . $index . "\n");
            $this->nodes[] = $leaf;
            $index = count($this->nodes) - 1;
            $this->nodes[$index]->key = $index;
//            print_r($this->nodes[$index]->key . " " . $index . "\n");

            $this->seen[$character] = $leaf;
            $node = $internalNode->parent;
        }

        while ($node !== null) {
            $largest = $this->findLargestNode($node->weight);
            if ($node !== $largest and $node !== $largest->parent and $node->parent != $largest) {
                $this->swapNodes($node, $largest);
            }
            $node->weight++;
            $node = $node->parent;
        }
//        echo "len: " . count($this->nodes);
    }

    private function findKey(Node $a): int|false
    {
        foreach ($this->nodes as $key => $b) {
            if ($a->symbol == $b->symbol and $a->weight == $b->weight) {
                return $key;
            }
        }
        return false;
    }

    private function swapNodes(Node $a, Node $b)
    {
//        $ai = array_keys($this->nodes, $a)[0];
//        $bi = array_keys($this->nodes, $b)[0];
//        $ai = $this->findKey($a);
//        $bi = $this->findKey($b);

        $ai = $a->key;
        $bi = $b->key;
        //swap nodes
        $temporary = &$this->nodes[$ai];
        $this->nodes[$ai] = &$this->nodes[$bi];
        $this->nodes[$bi] = &$temporary;

        //swap parents
        $temporaryParent = &$a->parent;
        $a->parent = &$b->parent;
        $b->parent = &$temporaryParent;

        //correct parent child
        if ($a->parent->left === $b) $a->parent->left = $a;
        else $a->parent->right = $a;
        if ($b->parent->left === $a) $b->parent->left = $b;
        else $b->parent->right = $b;
    }

    private function findLargestNode(int $weight): Node
    {
        foreach ($this->nodes as $node) {
            if ($node->weight === $weight) {
                return $node;
            }
        }
        return $this->nyt;
    }
}