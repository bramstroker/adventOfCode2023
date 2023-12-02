<?php
$lines = explode("\n", file_get_contents(__DIR__ . '/input.txt'));

$bag = new Bag([
    new CubeCount('red', 12),
    new CubeCount('green', 13),
    new CubeCount('blue', 14),
]);

$validGames = [];
foreach ($lines as $line) {
    $game = Game::parse($line);
    foreach ($game->draws as $draw) {
        $validGames[$game->id] = $game->id;
        $freshBag = clone $bag;
        try {
            foreach ($draw->cubeCounts as $cubeCount) {
                $freshBag->draw($cubeCount);
            }
        } catch (Exception $e) {
            unset($validGames[$game->id]);
            continue 2;
        }
    }
}
$idSum = array_reduce($validGames, fn(int $carry, int $gameNumber) => $carry + $gameNumber, 0);
echo 'Solution = ' . $idSum;

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