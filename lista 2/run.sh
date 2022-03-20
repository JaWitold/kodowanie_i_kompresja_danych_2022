#!/bin/bash


echo "encoding"
php -n ./index.php encode ./files/test0.bin ./out/test0.compressed
php -n ./index.php encode ./files/test1.bin ./out/test1.compressed
php -n ./index.php encode ./files/test2.bin ./out/test2.compressed
php -n ./index.php encode ./files/test3.bin ./out/test3.compressed
php -n ./index.php encode ./files/pan-tadeusz-czyli-ostatni-zajazd-na-litwie.txt ./out/pan-tadeusz-czyli-ostatni-zajazd-na-litwie.compressed

echo "decoding"
php -n ./index.php decode ./out/test0.compressed ./decoded/test0.bin
php -n ./index.php decode ./out/test1.compressed ./decoded/test1.bin
php -n ./index.php decode ./out/test2.compressed ./decoded/test2.bin
php -n ./index.php decode ./out/test3.compressed ./decoded/test3.bin
php -n ./index.php decode ./out/pan-tadeusz-czyli-ostatni-zajazd-na-litwie.compressed ./decoded/pan-tadeusz-czyli-ostatni-zajazd-na-litwie.txt
