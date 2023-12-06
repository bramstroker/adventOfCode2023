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

function parseRaces2(array $lines): array
{
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

function calculateWinWays(int $time, int $winningDistance)
{
    $numWays = 0;
    for ($i = 0; $i < $time; $i++) {
        //echo $i . PHP_EOL;
        $distance = $i * ($time - $i);
        //echo 'Final distance: ' . $distance . PHP_EOL;
        if ($distance > $winningDistance) {
            $numWays++;
        }
    }
    return $numWays;
}

function solve1(array $lines): int
{
    $races = parseRaces($lines);
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

function solve2(array $lines): int
{
    $races = parseRaces($lines, true);
    var_dump($races);
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

$solution1 = solve1($lines);
$solution2 = solve2($lines);

echo 'Solution 1 = ' . $solution1 . PHP_EOL;
echo 'Solution 2 = ' . $solution2 . PHP_EOL;