<?php
$lines = explode("\n", file_get_contents(__DIR__ . '/input.txt'));

$cards = array_map(fn($line) => Card::parse($line), $lines);

function solve1(array $cards): int
{
    return array_reduce($cards, fn(int $carry, Card $card) => $carry + calculateCardScore($card), 0);
}

function calculateCardScore(Card $card): int
{
    $winners = array_filter(array_unique(array_intersect($card->winningNumbers, $card->numbers)));
    $winCount = count($winners);
    if ($winCount === 0) {
        return 0;
    }

    return pow(2, abs(count($winners) - 1));
}

class Card {
    public function __construct(
        public array $numbers,
        public array $winningNumbers,
    ) {}

    public static function parse(string $input): Card
    {
        $cardData = substr($input, strpos($input, ':') + 1);
        [$numbers, $winning] = explode('|', $cardData);
        $numbers = array_map('intval', explode(' ', $numbers));
        $winning = array_map('intval', explode(' ', $winning));
        return new Card($numbers, $winning);
    }
}

$solution1 = solve1($cards);

echo 'Solution 1 = ' . $solution1 . PHP_EOL;