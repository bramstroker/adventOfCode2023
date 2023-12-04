<?php
$lines = explode("\n", file_get_contents(__DIR__ . '/input.txt'));

$cards = array_map(fn($line) => Card::parse($line), $lines);

function solve1(array $cards): int
{
    return array_reduce($cards, fn(int $carry, Card $card) => $carry + $card->calculateScore(), 0);
}

function solve2(array $cards): int
{
    $cardCount = array_fill(1, count($cards), 1);
    foreach ($cards as $card) {
        $winCount = $card->getWinCount();
        for ($i = ($card->cardNumber + 1); $i <= ($card->cardNumber + $winCount); $i++) {
            $cardCount[$i] += $cardCount[$card->cardNumber];
        }
    }

    return array_sum($cardCount);
}

class Card {
    public function __construct(
        public int $cardNumber,
        public array $numbers,
        public array $winningNumbers,
    ) {}

    public static function parse(string $input): Card
    {
        preg_match('/^Card\s+(\d+):([0-9\s]+)\|([0-9\s]+)/', $input, $matches);

        $numbers = array_map('intval', explode(' ', $matches[2]));
        $winning = array_map('intval', explode(' ', $matches[3]));
        return new Card($matches[1], $numbers, $winning);
    }

    public function getWinCount(): int
    {
        $winners = array_filter(array_unique(array_intersect($this->winningNumbers, $this->numbers)));
        return count($winners);
    }

    public function calculateScore(): int
    {
        $winCount = $this->getWinCount();
        if ($winCount === 0) {
            return 0;
        }

        return pow(2, abs($winCount - 1));
    }
}

$solution1 = solve1($cards);
$solution2 = solve2($cards);

echo 'Solution 1 = ' . $solution1 . PHP_EOL;
echo 'Solution 2 = ' . $solution2 . PHP_EOL;