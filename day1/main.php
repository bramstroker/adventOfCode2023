<?php
$lines = explode("\n", file_get_contents(__DIR__ . '/input.txt'));

$map = ['one' => 1, 'two' => 2, 'three' => 3, 'four' => 4, 'five' => 5, 'six' => 6, 'seven' => 7, 'eight' => 8, 'nine' => 9,];
$sum = 0;
foreach ($lines as $line) {
    $pattern = '/(?=(' . implode('|', array_keys($map)). '|\d))/';
    preg_match_all($pattern, $line, $matches);
    $numbers = array_map(fn($num) => $map[$num] ?? $num, $matches[1]);
    $sum += (int) ($numbers[0] . $numbers[count($numbers)-1]);
}
echo 'Solution = ' . $sum;