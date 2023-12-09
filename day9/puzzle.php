<?php
$lines = explode("\n", file_get_contents(__DIR__ . '/input2.txt'));

function buildSolveTree(array $line, array $tree): array
{
    $tree[] = $line;
    if (array_sum($line) === 0) {
        return $tree;
    }

    $nextLine = [];
    for ($i = 0; $i < count($line) - 1; $i++) {
        $diff = $line[$i + 1] - $line[$i];
        $nextLine[] = $diff;
    }

    return buildSolveTree($nextLine, $tree);
}

function printTree(array $tree): void
{
    foreach ($tree as $level => $nodes) {
        echo str_repeat(' ', $level) . implode(' ', $nodes) . PHP_EOL;
    }
}

function solve(array $lines): int
{
    $sum = 0;
    foreach ($lines as $line) {
        $tree = buildSolveTree(explode(' ', $line), []);

        $lastValues = array_map(fn(array $values) => end($values), $tree);
        $lastValues = array_reverse($lastValues);
        $result = array_reduce($lastValues, fn(int $previousSum, int $value) => $previousSum + $value, 0);
        //echo 'Result: ' . $result . PHP_EOL;
        $sum += $result;
    }
    return $sum;
}

function solve2(array $lines): int
{
    $sum = 0;
    foreach ($lines as $line) {
        $tree = buildSolveTree(explode(' ', $line), []);
        //printTree($tree);

        $firstValues = array_map(fn(array $values) => current($values), $tree);
        $firstValues = array_reverse($firstValues);
        $result = array_reduce($firstValues, fn(int $previousSum, int $value) => $value - $previousSum, 0);
        //echo 'Result: ' . $result . PHP_EOL;
        $sum += $result;
    }
    return $sum;
}

$solution1 = solve($lines);
$solution2 = solve2($lines);

echo 'Solution 1 = ' . $solution1 . PHP_EOL;
echo 'Solution 2 = ' . $solution2 . PHP_EOL;