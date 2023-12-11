<?php
$rows = explode("\n", file_get_contents(__DIR__ . '/input2.txt'));

function buildUniverse(array $rows): Universe
{
    $universe = [];
    $extraRows = [];
    foreach ($rows as $rowIndex => $row) {
        $columns = str_split($row);
        $universe[] = $columns;
        if (!str_contains($row, '#')) {
            $extraRows[] = $rowIndex;
        }
    }

    $extraColumns = [];
    foreach ($universe[0] as $column => $char) {
        $galaxyFound = false;
        for ($i = 0; $i < count($universe); $i++) {
            if ($universe[$i][$column] == '#') {
                $galaxyFound = true;
                break;
            }
        }
        if ($galaxyFound) {
            continue;
        }
        $extraColumns[] = $column;
    }

    return new Universe($universe, $extraRows, $extraColumns);
}

function solve(Universe $universe, $growSize = 1): int
{
    # Find all galaxy locations in the universe
    $galaxyLocations = [];
    foreach ($universe->grid as $row => $columns) {
        foreach ($columns as $column => $char) {
            if ($char == '#') {
                $galaxyLocations[] = [$row, $column];
            }
        }
    }

    # Calculate distances
    $pairsDone = [];
    $sum = 0;
    foreach ($galaxyLocations as $num1 => $pos1) {
        foreach ($galaxyLocations as $num2 => $pos2) {
            if ($num1 == $num2 || isset($pairsDone[$num1][$num2]) || isset($pairsDone[$num2][$num1])) {
                continue;
            }

            $colMin = min($pos1[1], $pos2[1]);
            $colMax = max($pos1[1], $pos2[1]);
            $rowMin = min($pos1[0], $pos2[0]);
            $rowMax = max($pos1[0], $pos2[0]);

            $extraRows = array_reduce($universe->extraRows, fn($carry, $row) => $row >= $rowMin && $row < $rowMax ? $carry + $growSize : $carry, 0);
            $extraCols = array_reduce($universe->extraColumns, fn($carry, $col) => $col >= $colMin && $col < $colMax ? $carry + $growSize : $carry, 0);

            $distance = abs(($rowMax + $extraRows) - $rowMin) + abs(($colMax + $extraCols) - $colMin);
            $sum += $distance;

            //echo 'Distance between ' . ($num1 + 1) . ' and ' . ($num2 + 1) . ' is ' . $distance . PHP_EOL;

            $pairsDone[$num1][$num2] = true;
        }
    }
    return $sum;
}

class Universe
{
    public function __construct(public $grid, public $extraRows, public $extraColumns)
    {
    }
}

$universe = buildUniverse($rows);

$solution1 = solve($universe);
$solution2 = solve($universe, 999999);

echo 'Solution 1 = ' . $solution1 . PHP_EOL;
echo 'Solution 2 = ' . $solution2 . PHP_EOL;