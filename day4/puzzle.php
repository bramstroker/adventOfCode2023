<?php
$lines = explode("\n", file_get_contents(__DIR__ . '/input.txt'));

$cards = array_map(fn($line) => Card::parse($line), $lines);

function solve1(array $cards): int
{
    return calculateTotalScore($cards);
}

function solve2(array $cards): int
{
    $numTotalCards = 0;
    foreach ($cards as $card) {
        echo 'Processing card ' . $card->cardNumber . PHP_EOL;
        $numTotalCards += 1;
        $extraCards = recurseWinExtraCards($card, $cards);
        $numTotalCards += count($extraCards);
    }

    return $numTotalCards;
}

function calculateTotalScore(array $cards): int
{
    return array_reduce($cards, fn(int $carry, Card $card) => $carry + $card->calculateScore(), 0);
}

function recurseWinExtraCards(Card $card, array $allCards, array &$extraWonCards = []): array
{
    $winCount = $card->getWinCount();
    if ($winCount === 0) {
        return [];
    }

    foreach (array_slice($allCards, $card->cardNumber, $winCount) as $extraCard) {
        $extraWonCards[] = $extraCard;
        recurseWinExtraCards($extraCard, $allCards, $extraWonCards);
    }
    return $extraWonCards;
}

class Card {
    public function __construct(
        public int $cardNumber,
        public array $numbers,
        public array $winningNumbers,
    ) {}

    public static function parse(string $input): Card
    {
        preg_match('/Card\s+(\d+):/', $input, $matches);

        $cardData = substr($input, strpos($input, ':') + 1);
        [$numbers, $winning] = explode('|', $cardData);
        $numbers = array_map('intval', explode(' ', $numbers));
        $winning = array_map('intval', explode(' ', $winning));
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