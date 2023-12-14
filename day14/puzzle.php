<?php

class Dish
{
    protected int $numRows;
    protected int $numCols;

    public function __construct(public array $grid)
    {
        $this->numRows = count($grid);
        $this->numCols = count($grid[0]);
    }

    public function tilt(Direction $direction): void
    {
        match ($direction) {
            Direction::NORTH => $this->tiltNorth(),
            Direction::SOUTH => $this->tiltSouth(),
            Direction::EAST => $this->tiltEast(),
            Direction::WEST => $this->tiltWest(),
        };
    }

    public function tiltNorth(): void
    {
        for ($col = 0; $col < $this->numCols; $col++) {
            for ($row = 0; $row < $this->numRows; $row++) {
                if ($this->grid[$row][$col] !== 'O') {
                    continue;
                }

                $newRow = $row;

                while ($newRow > 0 && $this->grid[$newRow - 1][$col] === '.') {
                    $oldVal = $this->grid[$newRow - 1][$col];
                    $newRow--;
                    $this->grid[$newRow][$col] = 'O';
                    $this->grid[$newRow + 1][$col] = $oldVal;
                }
            }
        }
    }

    public function tiltSouth(): void
    {
        for ($col = 0; $col < $this->numCols; $col++) {
            for ($row = $this->numRows - 1; $row >= 0; $row--) {
                if ($this->grid[$row][$col] !== 'O') {
                    continue;
                }

                $newRow = $row;

                while ($newRow < $this->numRows - 1 && $this->grid[$newRow + 1][$col] === '.') {
                    $oldVal = $this->grid[$newRow + 1][$col];
                    $newRow++;
                    $this->grid[$newRow][$col] = 'O';
                    $this->grid[$newRow - 1][$col] = $oldVal;
                }
            }
        }
    }

    public function tiltWest(): void
    {
        for ($row = 0; $row < $this->numRows; $row++) {
            for ($col = 0; $col < $this->numCols; $col++) {
                if ($this->grid[$row][$col] !== 'O') {
                    continue;
                }
                $newCol = $col;

                while ($newCol > 0 && $this->grid[$row][$newCol - 1] === '.') {
                    $oldVal = $this->grid[$row][$newCol - 1];
                    $newCol--;
                    $this->grid[$row][$newCol] = 'O';
                    $this->grid[$row][$newCol + 1] = $oldVal;
                }
            }
        }
    }

    public function tiltEast(): void
    {
        for ($row = 0; $row < $this->numRows; $row++) {
            for ($col = $this->numCols - 1; $col >= 0; $col--) {
                if ($this->grid[$row][$col] !== 'O') {
                    continue;
                }
                $newCol = $col;

                while ($newCol < $this->numCols - 1 && $this->grid[$row][$newCol + 1] === '.') {
                    $oldVal = $this->grid[$row][$newCol + 1];
                    $newCol++;
                    $this->grid[$row][$newCol] = 'O';
                    $this->grid[$row][$newCol - 1] = $oldVal;
                }
            }
        }
    }

    public function print(): string
    {
        $out = '';
        foreach ($this->grid as $row) {
            $out .= implode('', $row) . PHP_EOL;
        }
        return $out;
    }

    public static function parse(string $pattern): self
    {
        $rows = explode("\n", $pattern);
        $dish = array_map('str_split', $rows);
        return new self($dish);
    }

    public function calculateLoad(): int
    {
        $sum = 0;
        $multiplier = $this->numRows;
        foreach ($this->grid as $row) {
            $numRocks = array_reduce($row, fn($carry, $item) => $carry + ($item === 'O'), 0);
            //echo $numRocks . PHP_EOL;
            $sum += $numRocks * $multiplier;
            //echo $multiplier . PHP_EOL;
            //echo $sum . PHP_EOL;
            $multiplier--;
        }
        return $sum;
    }
}

enum Direction
{
    case NORTH;
    case EAST;
    case SOUTH;
    case WEST;
}

function solve(Dish $dish): int
{
    $dish->tiltNorth();
    return $dish->calculateLoad();
}

function solve2(Dish $dish): int
{
    $target = 1000000000;
    $cycle = 0;
    $seenHashes = [];
    while ($cycle++ < $target)
    {
        foreach ([Direction::NORTH, Direction::WEST, Direction::SOUTH, Direction::EAST] as $direction) {
            $dish->tilt($direction);
        }
        $hash = md5(serialize($dish->grid));
        if (isset($seenHashes[$hash]))
        {
            $cycleLength = $cycle - $seenHashes[$hash];
            echo 'Found a duplicate! at cycle ' . $cycle . ' with hash ' . $hash . PHP_EOL;
            echo 'Cycle length = ' . $cycleLength . PHP_EOL;

            $coverage = floor(($target - $cycle) / $cycleLength);
            echo 'Coverage = ' . $coverage . PHP_EOL;
            $cycle += $cycleLength * $coverage;
        }
        $seenHashes[$hash] = $cycle;
    }
    return $dish->calculateLoad();
}

$dish = Dish::parse(file_get_contents(__DIR__ . '/input2.txt'));

$solution1 = solve($dish);
$solution2 = solve2($dish);

echo 'Solution 1 = ' . $solution1 . PHP_EOL;
echo 'Solution 2 = ' . $solution2 . PHP_EOL;
