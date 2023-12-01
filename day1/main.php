<?php
$lines = explode("\n", file_get_contents(__DIR__ . '/input.txt'));

$map = ['one' => 1, 'two' => 2, 'three' => 3, 'four' => 4, 'five' => 5, 'six' => 6, 'seven' => 7, 'eight' => 8, 'nine' => 9,];
$sum = 0;
foreach ($lines as $line) {
    $pattern = '/(?=(' . implode('|', array_keys($map)). '|\d{1}))/';
    preg_match_all($pattern, $line, $matches);

    $firstNum = $map($matches[1][0]) ?? $matches[1][0];
    $secondNum = $matches[1][count($matches[1])-1];
    if (isset($map[$secondNum])) {
        $secondNum = $map[$secondNum];
    }

    $sum += (int) ($firstNum . $secondNum);
}
echo 'Solution = ' . $sum;