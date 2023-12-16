<?php
$lines = explode("\n", file_get_contents(__DIR__ . '/input2.txt'));

$grid = array_map(fn($line) => str_split($line), $lines);

enum Direction
{
    case UP;
    case DOWN;
    case LEFT;
    case RIGHT;

    public function isHorizontal(): bool
    {
        return in_array($this, [self::LEFT, self::RIGHT]);
    }

    public function isVertical(): bool
    {
        return in_array($this, [self::UP, self::DOWN]);
    }
}

function castRay(array &$grid, int $x, int $y, Direction $direction, array &$handled = [], array &$energized = []): array
{
    $key = $x . ',' . $y . ',' . $direction->name;
    if (in_array($key, $handled)) {
        return $energized;
    }
    while ($x >= 0 && $y >= 0 && $x < count($grid[0]) && $y < count($grid)) {

        //echo 'Casting ray at ' . $x . ',' . $y . PHP_EOL;
        $energized[$x . ',' . $y] = true;
        $char = $grid[$y][$x];
        if ($char == '.') {
            $grid[$y][$x] = getMarker($direction);
        }

        if ($char === '/') {
            $direction = match ($direction) {
                Direction::RIGHT => Direction::UP,
                Direction::LEFT => Direction::DOWN,
                Direction::DOWN => Direction::LEFT,
                Direction::UP => Direction::RIGHT,
            };
        }
        if ($char === '\\') {
            $direction = match ($direction) {
                Direction::RIGHT => Direction::DOWN,
                Direction::LEFT => Direction::UP,
                Direction::DOWN => Direction::RIGHT,
                Direction::UP => Direction::LEFT,
            };
        }
        if ($char === '|' && $direction->isHorizontal()) {
            castRay($grid, $x, $y - 1, Direction::UP, $handled, $energized);
            castRay($grid, $x, $y + 1, Direction::DOWN, $handled, $energized);
            break;
        }
        if ($char === '-' && $direction->isVertical()) {
            castRay($grid, $x - 1, $y, Direction::LEFT, $handled, $energized);
            castRay($grid, $x + 1, $y, Direction::RIGHT, $handled, $energized);
            break;
        }

        [$x, $y] = move($x, $y, $direction);
        $handled[] = $key;
    }
    return $energized;
}

function getMarker(Direction $direction): string {
    return match ($direction) {
        Direction::UP => '^',
        Direction::DOWN => 'v',
        Direction::LEFT => '<',
        Direction::RIGHT => '>',
    };
}

function move(int $x, int $y, Direction $direction): array
{
    return match ($direction) {
        Direction::UP => [$x, $y - 1],
        Direction::DOWN => [$x, $y + 1],
        Direction::LEFT => [$x - 1, $y],
        Direction::RIGHT => [$x + 1, $y],
    };
}

function printGrid(array $grid): string
{
    $output = '';
    foreach ($grid as $row) {
        $output .= implode('', $row) . PHP_EOL;
    }
    return $output;
}

function solve(array $grid): int
{
    $energized = castRay($grid, 0, 0, Direction::RIGHT);
    return count($energized);
}

function solve2(array $grid): int
{
    $countRows = count($grid);
    $countCols = count($grid[0]);

    $counts = [];
    for ($y = 0; $y < $countRows; $y++) {
        //echo 'Casting ray at 0, ' . $y . PHP_EOL;
        $energized = castRay($grid, 0, $y, Direction::RIGHT);
        $counts[] = count($energized);
        //echo 'Casting ray at ' . $countCols . ',' . $y . PHP_EOL;
        $energized = castRay($grid, $countCols - 1, $y, Direction::LEFT);
        $counts[] = count($energized);
    }
    for ($x = 0; $x < $countCols; $x++) {
        //echo 'Casting ray at ' . $x . ', 0' . PHP_EOL;
        $energized = castRay($grid, $x, 0, Direction::DOWN);
        $counts[] = count($energized);
        //echo 'Casting ray at ' . $x . ', ' . $countRows . PHP_EOL;
        $energized = castRay($grid, $x, $countRows - 1, Direction::UP);
        $counts[] = count($energized);
    }

    //echo printGrid($grid);
    return max($counts);
}

$solution1 = solve($grid);
$solution2 = solve2($grid);

echo 'Solution 1 = ' . $solution1 . PHP_EOL;
echo 'Solution 2 = ' . $solution2 . PHP_EOL;