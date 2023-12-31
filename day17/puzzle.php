<?php
$lines = explode("\n", file_get_contents(__DIR__ . '/input2.txt'));

$grid = array_map(fn($line) => str_split($line), $lines);

class PathFinder
{
    protected array $distances = [];
    protected array $visited = [];
    protected SplMinHeap $queue;

    public function __construct(public readonly array $grid)
    {
        $this->queue = new SplMinHeap();
    }

    public function runDijkstra(int $minDistance = 1, int $maxDistance = 3): PathInfo
    {
        $end = new Node(count($this->grid[0]) - 1, count($this->grid) - 1);
        $pathInfo = new PathInfo(new Node(0, 0), Direction::RIGHT);
        $this->queue->insert([0, $pathInfo]);
        while (!$this->queue->isEmpty())
        {
            $current = $this->queue->extract()[1];
            $x = $current->node->x;
            $y = $current->node->y;

            if ($x === $end->x && $y === $end->y) {
                return $current;
            }

            $key = implode('|', [$x, $y, $current->direction->name, $current->directionCount]);
            if (isset($this->visited[$key])) {
                continue;
            }
            $this->visited[$key] = true;

            foreach ($this->getNeighbours($current->node) as $neighbour)
            {
                $neighbourNode = $neighbour[0];
                $nx = $neighbourNode->x;
                $ny = $neighbourNode->y;

                $nDirection = $neighbour[1];
                # Increase neighbour direction count if direction is the same as current
                $nDirectionCount = ($nDirection == $current->direction ? $current->directionCount + 1 : 1);

                # Cannot reverse direction
                if ($nDirection->opposite() === $current->direction) {
                    continue;
                }

                # Skip if direction count is too high
                if ($nDirectionCount > $maxDistance) {
                    continue;
                }

                # Skip if direction count is too low
                if ($current->distance && $nDirection != $current->direction && $current->directionCount < $minDistance) {
                    continue;
                }

                # Skip if we are at the end and direction count is too low
                if ($nx === $end->x && $ny === $end->y && $nDirectionCount < $minDistance) {
                    continue;
                }

                $newDistance = $current->distance + $this->grid[$ny][$nx];

                if ($newDistance < ($this->distances[$nx][$ny] ?? PHP_INT_MAX)) {
                    $this->distances[$nx][$ny] = $newDistance;
                }
                $this->queue->insert([$newDistance, new PathInfo(new Node($nx, $ny), $nDirection, $nDirectionCount, $newDistance, $current)]);
            }
        }
        throw new \Exception('No path found');
    }

    public function getDistances(): array
    {
        return $this->distances;
    }

    protected function getNeighbours(Node $node): array
    {
        $x = $node->x;
        $y = $node->y;
        $neighbours = [
            [new Node($x, $y - 1), Direction::UP],
            [new Node($x + 1, $y), Direction::RIGHT],
            [new Node($x, $y + 1), Direction::DOWN],
            [new Node($x - 1, $y), Direction::LEFT],
        ];
        return array_filter(
            $neighbours,
            fn(array $neighbour) => isset($this->grid[$neighbour[0]->y][$neighbour[0]->x])
        );
    }

    public function visualizeDistances(): void
    {
        for ($y = 0; $y < count($this->distances[0]); $y++) {
            for ($x = 0; $x < count($this->distances); $x++) {
                echo str_pad($this->distances[$x][$y], 3, ' ', STR_PAD_LEFT) . ' ';
            }
            echo PHP_EOL;
        }
    }

    public function drawPath(PathInfo $pathInfo): void
    {
        $grid = $this->grid;
        while ($pathInfo->previous !== null) {
            $char = match ($pathInfo->direction) {
                Direction::UP => '↑',
                Direction::DOWN => '↓',
                Direction::LEFT => '←',
                Direction::RIGHT => '→',
            };
            $grid[$pathInfo->node->y][$pathInfo->node->x] = "\033[31m{$char}\033[0m";
            $pathInfo = $pathInfo->previous;
        }

        foreach ($grid as $row) {
            echo implode(' ', $row) . PHP_EOL;
        }
    }
}

enum Direction
{
    case UP;
    case DOWN;
    case LEFT;
    case RIGHT;

    public function opposite(): Direction
    {
        return match ($this) {
            Direction::UP => Direction::DOWN,
            Direction::DOWN => Direction::UP,
            Direction::LEFT => Direction::RIGHT,
            Direction::RIGHT => Direction::LEFT,
        };
    }
}

class Node
{
    public function __construct(public readonly int $x, public readonly int $y)
    {
    }

    public function __toString(): string
    {
        return implode(',', [$this->x, $this->y]);
    }
}

class PathInfo
{
    public function __construct(
        public readonly Node $node,
        public readonly Direction $direction,
        public readonly int $directionCount = 0,
        public readonly int $distance = 0,
        public readonly ?PathInfo $previous = null
    ) {
        return;
    }
}

function solve(array $grid): int
{
    $pathFinder = new PathFinder($grid);
    $pathInfo = $pathFinder->runDijkstra();
    //$pathFinder->drawPath($pathInfo);
    return $pathInfo->distance;
}

function solve2(array $grid): int
{
    $pathFinder = new PathFinder($grid);
    $pathInfo = $pathFinder->runDijkstra(4, 10);
    //echo PHP_EOL;
    //$pathFinder->drawPath($pathInfo);
    return $pathInfo->distance;
}

$solution1 = solve($grid);
$solution2 = solve2($grid);

echo 'Solution 1 = ' . $solution1 . PHP_EOL;
echo 'Solution 2 = ' . $solution2 . PHP_EOL;