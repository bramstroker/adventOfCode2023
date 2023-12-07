<?php

$lines = explode("\n", file_get_contents(__DIR__ . '/input2.txt'));

enum HandRank: int
{
    case HIGH_CARD = 1;
    case ONE_PAIR = 2;
    case TWO_PAIR = 3;
    case THREE_OF_A_KIND = 4;
    case FULL_HOUSE = 5;
    case FOUR_OF_A_KIND = 6;
    case FIVE_OF_A_KIND = 7;
}

class Hand
{
    private array $cards;
    private ?HandRank $handRank = null;

    public function __construct(string $hand, public int $bid)
    {
        $this->cards = str_split($hand);
    }

    public function getCards(): array
    {
        return $this->cards;
    }

    public static function parse(string $hand): Hand
    {
        $parts = explode(' ', $hand);
        return new Hand($parts[0], (int) $parts[1]);
    }

    public function __toString(): string
    {
        return implode('', $this->cards);
    }

    public function getHandRank(bool $allowJokers = false): HandRank
    {
        if ($this->handRank !== null) {
            return $this->handRank;
        }

        $cardCounts = array_count_values($this->cards);
        arsort($cardCounts);

        $numJokers = 0;
        if ($allowJokers) {
            $numJokers = $cardCounts['J'] ?? 0;
            unset($cardCounts['J']);
        }
        $cardCounts = array_values($cardCounts);

        $primaryCardCount = $cardCounts[0] ?? 0;
        $secondaryCardCount = $cardCounts[1] ?? 0;
        $this->handRank = match ([$primaryCardCount, $secondaryCardCount]) {
            [2, 0], [2, 1] => HandRank::ONE_PAIR,
            [2, 2] => HandRank::TWO_PAIR,
            [3, 0], [3, 1] => HandRank::THREE_OF_A_KIND,
            [3, 2] => HandRank::FULL_HOUSE,
            [4, 0], [4, 1] => HandRank::FOUR_OF_A_KIND,
            [5, 0] => HandRank::FIVE_OF_A_KIND,
            default => HandRank::HIGH_CARD,
        };

        if ($numJokers > 0) {
            $this->handRank = $this->upgradeHandForJokers($numJokers);
        }

        return $this->handRank;
    }

    protected function upgradeHandForJokers(int $numJokers): HandRank
    {
        if ($numJokers === 0) {
            return $this->handRank;
        }

        if ($numJokers === 1) {
            return match($this->handRank) {
                HandRank::HIGH_CARD => HandRank::ONE_PAIR,
                HandRank::ONE_PAIR => HandRank::THREE_OF_A_KIND,
                HandRank::TWO_PAIR => HandRank::FULL_HOUSE,
                HandRank::THREE_OF_A_KIND => HandRank::FOUR_OF_A_KIND,
                HandRank::FOUR_OF_A_KIND => HandRank::FIVE_OF_A_KIND,
                default => $this->handRank,
            };
        }

        if ($numJokers === 2) {
            return match($this->handRank) {
                HandRank::HIGH_CARD => HandRank::THREE_OF_A_KIND,
                HandRank::ONE_PAIR => HandRank::FOUR_OF_A_KIND,
                HandRank::THREE_OF_A_KIND => HandRank::FIVE_OF_A_KIND,
                default => $this->handRank,
            };
        }

        if ($numJokers === 3) {
            return match($this->handRank) {
                HandRank::HIGH_CARD => HandRank::FOUR_OF_A_KIND,
                HandRank::ONE_PAIR => HandRank::FIVE_OF_A_KIND,
                default => $this->handRank,
            };
        }

        return HandRank::FIVE_OF_A_KIND;
    }
}

class CardRanks
{
    private static array $cardRanks = [
        '2' => 0,
        '3' => 1,
        '4' => 2,
        '5' => 3,
        '6' => 4,
        '7' => 5,
        '8' => 6,
        '9' => 7,
        'T' => 8,
        'J' => 9,
        'Q' => 10,
        'K' => 11,
        'A' => 12,
    ];

    public static function getRank(string $card, bool $handleJoker = false): int
    {
        if ($card === 'J' && $handleJoker) {
            return -1;
        }
        return self::$cardRanks[$card];
    }
}

//debug('QJJ66', HandType::FOUR_OF_A_KIND);
//debug('Q5J5Q', HandType::FULL_HOUSE);
//debug('67JAJ',  HandType::THREE_OF_A_KIND);
//debug('JJJJA', HandType::FIVE_OF_A_KIND);
//debug('A4888', HandType::THREE_OF_A_KIND);

function debug(string $hand, HandRank $expectedHandType): void
{
    $hand = new Hand($hand, 1);
    echo 'hand: ' . $hand . PHP_EOL;
    assert ($hand->getHandRank(true) === $expectedHandType);
}

function solve(array $lines, bool $allowJokers = false): int
{
    $hands = [];
    foreach ($lines as $line) {
        $hand = Hand::parse($line);
        $hands[] = $hand;
    }

    usort($hands, function(Hand $a, Hand $b) use($allowJokers) {
        $comp = $a->getHandRank($allowJokers)->value <=> $b->getHandRank($allowJokers)->value;
        if ($comp !== 0) {
            return $comp;
        }
        foreach ($a->getCards() as $i => $card) {
            $comp = CardRanks::getRank($a->getCards()[$i], $allowJokers) <=> CardRanks::getRank($b->getCards()[$i], $allowJokers);
            if ($comp !== 0) {
                return $comp;
            }
        }
        return 0;
    });

    $result = 0;
    foreach ($hands as $i => $hand) {
        //echo $hand->bid . ' => ' . $multiplier . PHP_EOL;
        $handScore = $hand->bid * ($i + 1);
        $result += $handScore;
        //echo implode('', $hand->getCards()) . ' (' . $hand->getHandType($allowJokers)->name . ')' . PHP_EOL;
    }
    return $result;
}

$solution1 = solve($lines);
$solution2 = solve($lines, true);

echo 'Solution 1 = ' . $solution1 . PHP_EOL;
echo 'Solution 2 = ' . $solution2 . PHP_EOL;