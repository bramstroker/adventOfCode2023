<?php
/**
 * Input 1: example 1
 *
 * part1 102  correct
 * part2 94   correct
 *
 * Input 2: Real puzzle input
 *
 * part1 1039 correct
 * part2 1201 correct
 *
 * Input 3: example 2
 * part1 ?
 * part2 47    (should be 71)
 */

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
        $this->initialize();
    }

    protected function initialize(): void
    {
        foreach ($this->grid as $y => $line) {
            foreach ($line as $x => $value) {
                $distance = PHP_INT_MAX;
                if ($x === 0 && $y === 0) {
                    $distance = 0;
                    $pathInfo = new PathInfo(new Node($x, $y), Direction::RIGHT);
                    $this->queue->insert([$distance, $pathInfo]);

                }
                $this->distances[$x][$y] = $distance;
            }
        }
    }

    public function runDijkstra(int $minDistance = 1, int $maxDistance = 3): int
    {
        $end = new Node(count($this->grid[0]) - 1, count($this->grid) - 1);
        while (!$this->queue->isEmpty())
        {
            $current = $this->queue->extract()[1];
            $x = $current->node->x;
            $y = $current->node->y;

            $key = implode('|', [$x, $y, $current->direction->name, $current->directionCount]);
            if (isset($this->visited[$key])) {
                continue;
            }
            $this->visited[$key] = true;

            foreach ($this->getNeighbours(new Node($x, $y)) as $neighbour)
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

                if ($newDistance < $this->distances[$nx][$ny]) {
                    $this->distances[$nx][$ny] = $newDistance;
                }
                $this->queue->insert([$newDistance, new PathInfo(new Node($nx, $ny), $nDirection, $nDirectionCount, $newDistance)]);
            }
        }
        return $this->distances[$end->x][$end->y];
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
    ) {
        return;
    }
}

function solve(array $grid): int
{
    $pathFinder = new PathFinder($grid);
    $answer = $pathFinder->runDijkstra();
    //$pathFinder->visualizeDistances();
    return $answer;
}

function solve2(array $grid): int
{
    $pathFinder = new PathFinder($grid);
    return $pathFinder->runDijkstra(4, 10);
}

$solution1 = solve($grid);
$solution2 = solve2($grid);

echo 'Solution 1 = ' . $solution1 . PHP_EOL;
echo 'Solution 2 = ' . $solution2 . PHP_EOL;