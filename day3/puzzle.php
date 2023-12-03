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
                $directions = [Direction::Top, Direction::Bottom];
                if ($x === ($endOffset - 1)) {
                    $directions[] = Direction::Right;
                }
                if ($x === $offset) {
                    $directions[] = Direction::Left;
                }
                $test = fn(string $char) => $char !== '.';
                if (!empty(findAdjacent($grid, $x, $y, $test, $directions))) {
                    echo 'Symbol found adjacent to ' . $number . ' at ' . $x . ',' . $y . PHP_EOL;
                    $sum += $number;
                    continue 2;
                }
            }
        }
    }
    return $sum;
}

function solve2(array $grid): int
{
    $sum = 0;
    foreach ($grid as $y => $line) {
        foreach ($line as $x => $char) {
            if ($char !== '*') {
                continue;
            }

            # Scan adjacent bounding box for full numbers
            $numbersAt = [
                ...findNumberCoordsAtLine($grid, $y-1, $x-1, $x+1),
                ...findNumberCoordsAtLine($grid, $y, $x-1, $x+1),
                ...findNumberCoordsAtLine($grid, $y+1, $x-1, $x+1),
            ];

            $surroundingGrid = printSurroundingGrid($grid, new Coordinate($x, $y));
            echo $surroundingGrid;

            if (count($numbersAt) !== 2) {
                echo 'No Gear found at ' . $y . ',' . $x . ', numberCount = ' . count($numbersAt) . PHP_EOL;
                continue;
            }

            $number1 = resolveFullNumber($grid, $numbersAt[0]);
            $number2 = resolveFullNumber($grid, $numbersAt[1]);
            $gearRatio = $number1 * $number2;

            echo 'Gear found at ' . $y . ',' . $x . PHP_EOL;
            echo 'Gear numbers = ' . $number1 . ',' . $number2 . PHP_EOL;
            echo 'Gear ratio = ' . $gearRatio . PHP_EOL;
            echo PHP_EOL;

            $sum += $gearRatio;
        }
    }
    return $sum;
}

function findNumberCoordsAtLine(array $grid, int $y, int $minX, int $maxX): array
{
    $line = '';
    for ($x = $minX; $x <= $maxX; $x++) {
        $line .= $grid[$y][$x];
    }
    preg_match_all('/\d+/', $line, $matches, PREG_OFFSET_CAPTURE);
    if (!count($matches[0])) {
        return [];
    }

    return array_map(fn($match) => new Coordinate($match[1] + $minX, $y), $matches[0]);
}

function printSurroundingGrid(array $grid, Coordinate $coordinate): string
{
    $output = '';
    for ($y = $coordinate->y - 1; $y <= $coordinate->y + 2; $y++) {
        $line = '';
        for ($x = $coordinate->x - 3; $x <= $coordinate->x + 3; $x++) {
            $line .= $grid[$y][$x] ?? ' ';
        }
        $output .= $line . PHP_EOL;
    }
    return $output;
}

function findAdjacent(
    array $grid,
    int $x,
    int $y,
    callable $test,
    array $directions = [Direction::Top, Direction::Left, Direction::Right, Direction::Bottom]
): array {
    $coordsToTest = [
        in_array(Direction::Right, $directions) ? [$y, $x+1] : null,
        in_array(Direction::Left, $directions) ? [$y, $x-1] : null,
        in_array(Direction::Top, $directions) ? [$y-1, $x] : null,
        in_array(Direction::Top, $directions) ? [$y-1, $x-1] : null,
        in_array(Direction::Top, $directions) ? [$y-1, $x+1] : null,
        in_array(Direction::Bottom, $directions) ? [$y+1, $x] : null,
        in_array(Direction::Bottom, $directions) ? [$y+1, $x-1] : null,
        in_array(Direction::Bottom, $directions) ? [$y+1, $x+1] : null,
    ];

    $found = [];
    foreach (array_filter($coordsToTest) as $yx) {
        $value = $grid[$yx[0]][$yx[1]] ?? null;
        if ($value !== null && $test($value)) {
            $found[] = new Coordinate($yx[1], $yx[0]);
        }
    }
    return $found;
}

function resolveFullNumber(array $grid, Coordinate $coord): int
{
    $x = $coord->x;
    $y = $coord->y;
    $gridLine = $grid[$y];
    $numberStart = $x;
    for ($i = $x; $i >= 0; $i--) {
        if (!is_numeric($gridLine[$i])) {
            break;
        }
        $numberStart = $i;
    }

    $number = '';
    for ($i = $numberStart; $i < count($gridLine); $i++) {
        if (!is_numeric($gridLine[$i])) {
            break;
        }
        $number .= $gridLine[$i];
    }
    return (int) $number;
}

enum Direction {
    case Top;
    case Left;
    case Right;
    case Bottom;
}

class Coordinate {
    public function __construct(
        public int $x,
        public int $y
    ) {}
}

$solution1 = solve1($grid);
$solution2 = solve2($grid);

echo 'Solution 1 = ' . $solution1 . PHP_EOL;
echo 'Solution 2 = ' . $solution2 . PHP_EOL;