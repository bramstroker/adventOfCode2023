<?php
$lines = explode("\n", file_get_contents(__DIR__ . '/input.txt'));

$grid = array_map(fn($line) => str_split($line), $lines);

function solve1(array $grid): int
{
    $sum = 0;
    foreach ($grid as $y => $line) {
        preg_match_all('/\d+/', implode("", $line), $matches, PREG_OFFSET_CAPTURE);

        foreach ($matches[0] as $match) {
            $number = $match[0];
            $offset = $match[1];
            $endOffset = $offset + strlen($number);
            for ($x = $offset; $x < $endOffset; $x++) {
                $testRight = $x === ($endOffset - 1);
                $testLeft = $x === $offset;
                if (is_surrounded_by_symbol($grid, $x, $y, $testLeft, $testRight)) {
                    echo 'Symbol found adjacent to ' . $number . ' at ' . $x . ',' . $y . PHP_EOL;
                    $sum += $number;
                    continue 2;
                }
            }
        }
    }
    return $sum;
}

function is_surrounded_by_symbol(
    array $grid,
    int $x,
    int $y,
    bool $testLeft = true,
    bool $testRight = true
): bool {
    $right = $testRight ? ($grid[$y][$x+1] ?? '.') : '.';
    $left = $testLeft ? ($grid[$y][$x-1] ?? '.') : '.';
    return (
        $left !== '.' ||
        $right !== '.' ||
        ($grid[$y-1][$x] ?? '.') !== '.' ||
        ($grid[$y-1][$x-1] ?? '.') !== '.' ||
        ($grid[$y-1][$x+1] ?? '.') !== '.' ||
        ($grid[$y+1][$x] ?? '.') !== '.' ||
        ($grid[$y+1][$x-1] ?? '.') !== '.' ||
        ($grid[$y+1][$x+1] ?? '.') !== '.'
    );
}

echo 'Solution 1 = ' . solve1($grid) . PHP_EOL;