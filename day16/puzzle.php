<?php
$string = file_get_contents(__DIR__ . '/input2.txt');

$steps = explode(",", $string);

function calculateHash(string $string): int
{
    $result = 0;
    for ($i = 0; $i < strlen($string); $i++) {
        $result = ($result + ord($string[$i])) * 17 % 256;
    }
    return $result;
}

function solve(array $steps): int
{
    return array_reduce($steps, fn($sum, $string) => $sum + calculateHash($string), 0);
}

function solve2(array $steps): int
{
    $boxes = [];

    foreach ($steps as $step) {
        preg_match('/([-a-z]+)([-=])([0-9]+)?/', $step, $matches);
        $label = $matches[1];
        $box = calculateHash($label);
        $operation = $matches[2];
        $focalLength = $matches[3] ?? null;

        if ($operation == '=') {
            $boxes[$box][$label] = $focalLength;
        }

        if ($operation == '-') {
            unset($boxes[$box][$label]);
        }
    }

    $sum = 0;
    foreach ($boxes as $box => $lenses) {
        $lensIndex = 1;
        foreach ($lenses as $focalLength) {
            $sum += ($box + 1) * $lensIndex * $focalLength;
            $lensIndex++;
        }
    }

    return $sum;
}

$solution1 = solve($steps);
$solution2 = solve2($steps);

echo 'Solution 1 = ' . $solution1 . PHP_EOL;
echo 'Solution 2 = ' . $solution2 . PHP_EOL;