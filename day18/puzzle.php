<?php
$lines = explode("\n", file_get_contents(__DIR__ . '/input.txt'));

function parseInstructions(array $lines): array
{
    $instructions = [];
    foreach ($lines as $line) {
        //R 6 (#70c710)
        preg_match('/([A-Z]) (\d+) \((#[a-f0-9]{6})\)/', $line, $matches);
        $instructions[] = new Instruction(
            Direction::from($matches[1]),
            (int)$matches[2],
        );
    }
    return $instructions;
}

function parseInstructions2(array $lines): array
{
    $instructions = [];
    foreach ($lines as $line) {
        //R 6 (#70c710)
        preg_match('/[A-Z] \d+ \(#([a-f0-9]{5})([0-4])\)/', $line, $matches);
        $direction = match($matches[2]) {
            '3' => Direction::UP,
            '1' => Direction::DOWN,
            '2' => Direction::LEFT,
            '0' => Direction::RIGHT,
        };
        $instructions[] = new Instruction(
            $direction,
            hexdec($matches[1]),
        );
    }
    return $instructions;
}

class Instruction
{
    public function __construct(
        public readonly Direction $direction,
        public readonly int $distance
    ) {
    }
}

enum Direction:string
{
    case UP = 'U';
    case DOWN = 'D';
    case LEFT = 'L';
    case RIGHT = 'R';
}

function solve(array $instructions): int
{
    $sum1 = $sum2 = 0;
    $x = $y = 0;
    $outline = 0;
    $prevX = $prevY = 0;

    foreach ($instructions as $instruction) {
        switch ($instruction->direction) {
            case Direction::UP:
                $y -= $instruction->distance;
                break;
            case Direction::DOWN:
                $y += $instruction->distance;
                break;
            case Direction::LEFT:
                $x -= $instruction->distance;
                break;
            case Direction::RIGHT:
                $x += $instruction->distance;
                break;
        }

        $outline += $instruction->distance;
        $sum1 += $x * $prevY;
        $sum2 += $y * $prevX;
        $prevX = $x;
        $prevY = $y;
    }

    return (abs($sum1 - $sum2) + $outline) / 2 + 1;
}

$solution1 = solve(parseInstructions($lines));
$solution2 = solve(parseInstructions2($lines));

echo 'Solution 1 = ' . $solution1 . PHP_EOL;
echo 'Solution 2 = ' . $solution2 . PHP_EOL;