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
                if (find_adjacent($grid, $x, $y, $test, $directions) !== null) {
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

            $numbersAt = array_values(array_filter([
                find_adjacent($grid, $x, $y, 'is_numeric', [Direction::Top]),
                find_adjacent($grid, $x, $y, 'is_numeric', [Direction::Bottom]),
                find_adjacent($grid, $x, $y, 'is_numeric', [Direction::Left]),
                find_adjacent($grid, $x, $y, 'is_numeric', [Direction::Right]),
            ]));
            if (count($numbersAt) !== 2) {
                echo 'No Gear found at ' . $y . ',' . $x . ', numberCount = ' . count($numbersAt) . PHP_EOL;
                continue;
            }

            $number1 = resolveNumber($grid, $numbersAt[0]);
            $number2 = resolveNumber($grid, $numbersAt[1]);
            $gearRatio = $number1 * $number2;

            echo 'Gear found at ' . $y . ',' . $x . PHP_EOL;
            echo 'Gear numbers = ' . $number1 . ',' . $number2 . PHP_EOL;
            echo 'Gear ratio = ' . $gearRatio . PHP_EOL;

            $sum += $gearRatio;
        }
    }
    return $sum;
}

function find_adjacent(
    array $grid,
    int $x,
    int $y,
    callable $test,
    array $directions = [Direction::Top, Direction::Left, Direction::Right, Direction::Bottom]
): ?Coordinate {
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

    foreach (array_filter($coordsToTest) as $yx) {
        $value = $grid[$yx[0]][$yx[1]] ?? null;
        if ($value !== null && $test($value)) {
            return new Coordinate($yx[1], $yx[0]);
        }
    }
    return null;
}

function resolveNumber(array $grid, Coordinate $coord): int
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

echo 'Solution 1 = ' . solve1($grid) . PHP_EOL;
echo 'Solution 1 = ' . solve2($grid) . PHP_EOL;