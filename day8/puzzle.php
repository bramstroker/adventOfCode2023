<?php
$lines = explode("\n", file_get_contents(__DIR__ . '/input2.txt'));

function solve(array $lines): int
{
    $instructions = str_split($lines[0]);

    $lookup = [];
    for ($i = 2; $i < count($lines); $i++) {
        //AAA = (BBB, CCC)
        preg_match('/(.*) = \((.*), (.*)\)/', $lines[$i], $matches);
        $lookup[$matches[1]] = [$matches[2], $matches[3]];
    }

    return resolvePath($lookup, $instructions, 'AAA', fn($location) => $location === 'ZZZ');
}

function solve2(array $lines): int
{
    $instructions = str_split($lines[0]);

    $lookup = [];
    $startingNodes = [];
    for ($i = 2; $i < count($lines); $i++) {
        //AAA = (BBB, CCC)
        preg_match('/(.*) = \((.*), (.*)\)/', $lines[$i], $matches);
        $lookup[$matches[1]] = [$matches[2], $matches[3]];
        if (str_ends_with($matches[1], 'A')) {
            $startingNodes[] = $matches[1];
        }
    }

    $stepSizes = [];
    foreach ($startingNodes as $start) {
        $steps = resolvePath($lookup, $instructions, $start, fn($location) => str_ends_with($location, 'Z'));
        $stepSizes[] = $steps;
    }

    return calculateLcm($stepSizes);
}

function gcd($a, $b): int
{
    if ($b == 0)
        return $a;
    return gcd($b, $a % $b);
}

function calculateLcm($stepSizes): int
{
    $result = $stepSizes[0];
    foreach ($stepSizes as $val)
        $result = ((($val * $result)) / (gcd($val, $result)));
    return $result;
}


function resolvePath(array $lookup, array $instructions, string $start, callable $destinationCheck): int
{
    $steps = 0;
    $location = $start;
    while (!$destinationCheck($location)) {
        $instruction = array_shift($instructions);
        array_push($instructions, $instruction);
        $location = ($instruction === 'L') ? $lookup[$location][0] : $lookup[$location][1];
        $steps++;
    }

    return $steps;
}

$solution1 = solve($lines);
$solution2 = solve2($lines);

echo 'Solution 1 = ' . $solution1 . PHP_EOL;
echo 'Solution 2 = ' . $solution2 . PHP_EOL;