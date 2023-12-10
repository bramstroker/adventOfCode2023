<?php
$lines = explode("\n", file_get_contents(__DIR__ . '/input3.txt'));



function solve(array $pipeLoop): int
{
    return ceil(count($pipeLoop) / 2);
}

function solve2(PipeGrid $pipeGrid, array $pipeLoop): int
{
    $totalInside = 0;
    foreach ($pipeGrid->grid as $y => $line) {
        $countInside = 0;
        $inside = false;
        foreach ($line as $x => $pipe) {
            $point = new Point($x, $y);
            if (in_array($point, $pipeLoop)) {
                if (in_array($pipe, ['|', 'J', 'L'])) {
                    $inside = !$inside;
                }
                continue;
            }

            if ($inside) {
                $countInside++;
            }
        }
        //echo 'Line ' . $y . ': ' . $countInside . PHP_EOL;

        $totalInside += $countInside;
    }
    return $totalInside;
}

class PipeGrid
{
    public function __construct(
        public readonly array $grid,
        private readonly array $startingPoints
    ) {
    }

    public function findMainLoop(): ?array
    {
        foreach ($this->startingPoints as $start) {
            $loop = null;
            foreach ($this->getStartNeighbours($start) as $current) {
                //echo 'Trying start at ' . $current . PHP_EOL;
                $previous = $start;
                $loop = [$start];
                while ($current != $start) {
                    //$this->printGrid($loop);
                    try {
                        $next = $this->getNextPoint($previous, $current);
                    } catch (Exception $e) {
                        echo 'Failed at ' . $current . PHP_EOL;
                        continue 2;
                    }
                    //echo 'Navigating from ' . $current . ' to ' . $next . PHP_EOL;
                    $loop[] = $current;
                    $previous = $current;
                    $current = $next;
                }
            }
            return $loop;
        }
        throw new Exception('No loop found');
    }

    public function getStartNeighbours(Point $point): array
    {
        return array_filter(
            [
                new Point($point->x - 1, $point->y),
                new Point($point->x + 1, $point->y),
                new Point($point->x, $point->y - 1),
                new Point($point->x, $point->y + 1),
            ],
            fn(Point $point) => $this->isValid($point) && !in_array($this->getPipeAt($point), ['.', 'S'])
        );
    }

    private function getNextPoint(Point $previous, Point $current): Point
    {
        $direction = null;
        if ($current->x > $previous->x) {
            $direction = Direction::RIGHT;
        } elseif ($current->x < $previous->x) {
            $direction = Direction::LEFT;
        } elseif ($current->y > $previous->y) {
            $direction = Direction::DOWN;
        } elseif ($current->y < $previous->y) {
            $direction = Direction::UP;
        }

        $pipe = $this->getPipeAt($current);
        //echo 'Pipe at ' . $current . ' is ' . $pipe . ' and direction is ' . $direction?->name . PHP_EOL;

        return match ([$pipe, $direction]) {
            ['|', Direction::UP], ['L', Direction::LEFT], ['J', Direction::RIGHT] => new Point($current->x, $current->y - 1),
            ['|', Direction::DOWN], ['7', Direction::RIGHT], ['F', Direction::LEFT] => new Point($current->x, $current->y + 1),
            ['-', Direction::LEFT], ['J', Direction::DOWN] , ['7', Direction::UP]=> new Point($current->x - 1, $current->y),
            ['-', Direction::RIGHT], ['L', Direction::DOWN], ['F', Direction::UP] => new Point($current->x + 1, $current->y),
            default => throw new Exception('Invalid pipe/direction combination'),
        };
    }

    public function isValid(Point $point): bool
    {
        return isset($this->grid[$point->y][$point->x]);
    }

    public function getPipeAt(Point $point): string
    {
        return $this->grid[$point->y][$point->x];
    }

    public function printGridAt(Point $point, int $radius): void
    {
        $minX = $point->x - $radius;
        $maxX = $point->x + $radius;
        $minY = $point->y - $radius;
        $maxY = $point->y + $radius;

        for ($y = $minY; $y <= $maxY; $y++) {
            for ($x = $minX; $x <= $maxX; $x++) {
               if ($this->isValid(new Point($x, $y))) {
                    echo $this->getPipeAt(new Point($x, $y));
                } else {
                    echo ' ';
                }
            }
            echo PHP_EOL;
        }
    }

    public static function createFromInput($lines): PipeGrid
    {
        $grid = [];
        $startingPoints = [];
        foreach ($lines as $y => $line) {
            foreach (str_split($line) as $x => $pipe) {
                $grid[$y][$x] = $pipe;
                if ($pipe === 'S') {
                    $startingPoints[] = new Point($x, $y);
                }
            }
        }

        return new self($grid, $startingPoints);
    }
}


class Point
{
    public function __construct(public int $x, public int $y)
    {
    }

    public function __toString(): string
    {
        return '(' . $this->y . ', ' . $this->x . ')';
    }
}

enum Direction
{
    case UP;
    case DOWN;
    case LEFT;
    case RIGHT;
}

class GridPrinter
{
    protected array $characterMap = [
        'S' => 'S',
        ' ' => ' ',
        '.' => '.',
        '|' => '║',
        '-' => '═',
        'J' => '╝',
        'L' => '╚',
        '7' => '╗',
        'F' => '╔',
    ];
    public function format(PipeGrid $grid): string
    {
        $formatted = '';
        foreach ($grid->grid as $line) {
            foreach ($line as $pipe) {
                $formatted .= $this->characterMap[$pipe];
            }
            $formatted .= PHP_EOL;
        }
        return $formatted;
    }
}

$pipeGrid = PipeGrid::createFromInput($lines);
$loop = $pipeGrid->findMainLoop();
echo (new GridPrinter())->format($pipeGrid);

$solution1 = solve($loop);
$solution2 = solve2($pipeGrid, $loop);

echo 'Solution 1 = ' . $solution1 . PHP_EOL;
echo 'Solution 2 = ' . $solution2 . PHP_EOL;