<?php
$lines = explode("\n", file_get_contents(__DIR__ . '/input.txt'));

function parseRaces(array $lines, bool $removeSpaces = false): array
{
    if ($removeSpaces) {
        $lines = array_map(function ($line) {
            return str_replace(' ', '', $line);
        }, $lines);
    }

    preg_match_all('/\d+/', $lines[0], $timeMatches);
    preg_match_all('/\d+/', $lines[1], $distanceMatches);
    $races = [];
    foreach ($timeMatches[0] as $key => $time) {
        $races[] = [
            'time' => $time,
            'distance' => $distanceMatches[0][$key],
        ];
    }
    return $races;
}

function calculateWinWays(int $time, int $winningDistance): int
{
    $numWays = 0;
    for ($i = 0; $i < $time; $i++) {
        $distance = $i * ($time - $i);
        if ($distance > $winningDistance) {
            $numWays++;
        }
    }
    return $numWays;
}

function solve(array $races): int
{
    $result = 0;
    foreach ($races as $race) {
        $ways = calculateWinWays($race['time'], $race['distance']);
        //echo 'Ways: ' . $ways . PHP_EOL;
        if ($result === 0) {
            $result = $ways;
        } else {
            $result *= $ways;
        }
    }
    return $result;
}

$solution1 = solve(parseRaces($lines));
$solution2 = solve(parseRaces($lines, true));

echo 'Solution 1 = ' . $solution1 . PHP_EOL;
echo 'Solution 2 = ' . $solution2 . PHP_EOL;