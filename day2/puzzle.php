<?php
$lines = explode("\n", file_get_contents(__DIR__ . '/input.txt'));

function solve1(array $lines): int
{
    $createBag = function() {
        return new Bag([
            new CubeCount('red', 12),
            new CubeCount('green', 13),
            new CubeCount('blue', 14),
        ]);
    };

    $validGames = [];
    foreach ($lines as $line) {
        $game = Game::parse($line);
        foreach ($game->draws as $draw) {
            $validGames[$game->id] = $game->id;
            $bag = $createBag();
            try {
                foreach ($draw->cubeCounts as $cubeCount) {
                    $bag->draw($cubeCount);
                }
            } catch (Exception $e) {
                unset($validGames[$game->id]);
                continue 2;
            }
        }
    }
    return array_reduce($validGames, fn(int $carry, int $gameNumber) => $carry + $gameNumber, 0);
}

function solve2(array $lines): int
{
    $sum = 0;
    foreach ($lines as $line) {
        $game = Game::parse($line);

        $highestCountPerColor = [];
        foreach ($game->draws as $draw) {
            foreach ($draw->cubeCounts as $cubeCount) {
                if (!isset($highestCountPerColor[$cubeCount->color]) || $highestCountPerColor[$cubeCount->color] < $cubeCount->amount) {
                    $highestCountPerColor[$cubeCount->color] = $cubeCount->amount;
                }
            }
        }
        $sum += array_product($highestCountPerColor);
    }
    return $sum;
}

echo 'Solution 1 = ' . solve1($lines) . PHP_EOL;
echo 'Solution 2 = ' . solve2($lines) . PHP_EOL;

class CubeCount
{
    public function __construct(public string $color, public int $amount)
    {
    }

    public static function parse(string $string): CubeCount
    {
        $parts = explode(' ', $string);
        return new CubeCount(trim($parts[1]), (int) trim($parts[0]));
    }
}

class Bag
{
    public function __construct(public array $cubeCounts)
    {
    }

    public function draw(CubeCount $cubeCount): void
    {
        foreach ($this->cubeCounts as $bagCount) {
            if ($bagCount->color === $cubeCount->color) {
                $bagCount->amount -= $cubeCount->amount;
                if ($bagCount->amount < 0) {
                    throw new \Exception('Not enough cubes in bag');
                }
            }
        }
    }

    public function __clone()
    {
        $this->cubeCounts = array_map(fn(CubeCount $cubeCount) => clone $cubeCount, $this->cubeCounts);
    }
}

class Draw
{
    public function __construct(public array $cubeCounts)
    {
    }
}

class Game
{
    /**
     * @param int    $id
     * @param Draw[] $draws
     */
    public function __construct(public int $id, public array $draws)
    {
    }

    public static function parse($string): Game
    {
        $draws = [];
        preg_match('/Game ([0-9]+): (.*)/', $string, $matches);
        foreach (explode(';', $matches[2]) as $parsedDraw) {
            $cubeCounts = [];
            foreach (explode(',', $parsedDraw) as $cubeCount) {
                $cubeCounts[] = CubeCount::parse(trim($cubeCount));
            }
            $draws[] = new Draw($cubeCounts);
        }
        return new Game($matches[1], $draws);
    }
}