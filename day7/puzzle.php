<?php

$lines = explode("\n", file_get_contents(__DIR__ . '/input2.txt'));

enum HandType: int
{
    case SINGLE = 1;
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
    private ?HandType $handType = null;

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

    public function getHandType(bool $allowJokers = false): HandType
    {
        if ($this->handType !== null) {
            return $this->handType;
        }

        $cardCounts = array_count_values($this->cards);
        arsort($cardCounts);

        $numJokers = 0;
        if ($allowJokers) {
            $numJokers = $cardCounts['J'] ?? 0;
            unset($cardCounts['J']);
        }
        $cardCounts = array_values($cardCounts);

        $this->handType = HandType::SINGLE;
        $primaryCardCount = $cardCounts[0] ?? 0;
        if ($primaryCardCount === 2) {
            $this->handType = HandType::ONE_PAIR;
            if (($cardCounts[1] ?? 0) == 2) {
                $this->handType = HandType::TWO_PAIR;
            }
        } elseif ($primaryCardCount === 3) {
            $this->handType = HandType::THREE_OF_A_KIND;
            if (($cardCounts[1] ?? 0) == 2) {
                $this->handType = HandType::FULL_HOUSE;
            }
        } elseif ($primaryCardCount === 4) {
            $this->handType = HandType::FOUR_OF_A_KIND;
        } elseif ($primaryCardCount === 5) {
            $this->handType = HandType::FIVE_OF_A_KIND;
        }

        if ($numJokers > 0) {
            $this->handType = $this->upgradeHandForJokers($numJokers);
        }

        return $this->handType;
    }

    protected function upgradeHandForJokers(int $numJokers): HandType
    {
        if ($numJokers === 0) {
            return $this->handType;
        }

        if ($numJokers === 1) {
            return match($this->handType) {
                HandType::SINGLE => HandType::ONE_PAIR,
                HandType::ONE_PAIR => HandType::THREE_OF_A_KIND,
                HandType::TWO_PAIR => HandType::FULL_HOUSE,
                HandType::THREE_OF_A_KIND => HandType::FOUR_OF_A_KIND,
                HandType::FOUR_OF_A_KIND => HandType::FIVE_OF_A_KIND,
                default => $this->handType,
            };
        }

        if ($numJokers === 2) {
            return match($this->handType) {
                HandType::SINGLE => HandType::THREE_OF_A_KIND,
                HandType::ONE_PAIR => HandType::FOUR_OF_A_KIND,
                HandType::THREE_OF_A_KIND => HandType::FIVE_OF_A_KIND,
                default => $this->handType,
            };
        }

        if ($numJokers === 3) {
            return match($this->handType) {
                HandType::SINGLE => HandType::FOUR_OF_A_KIND,
                HandType::ONE_PAIR => HandType::FIVE_OF_A_KIND,
                default => $this->handType,
            };
        }

        return HandType::FIVE_OF_A_KIND;
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

function debug(string $hand, HandType $expectedHandType): void
{
    $hand = new Hand($hand, 1);
    echo 'hand: ' . $hand . PHP_EOL;
    assert ($hand->getHandType(true) === $expectedHandType);
}

function solve(array $lines, bool $allowJokers = false): int
{
    $hands = [];
    foreach ($lines as $line) {
        $hand = Hand::parse($line);
        $hands[] = $hand;
    }

    usort($hands, function(Hand $a, Hand $b) use($allowJokers) {
        $comp = $a->getHandType($allowJokers)->value <=> $b->getHandType($allowJokers)->value;
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