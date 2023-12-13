<?php
$patterns = explode("\n\n", file_get_contents(__DIR__ . '/input2.txt'));

function findMirrorLocation(array $rows): ?int
{
    foreach ($rows as $i => $row) {
        if ($row === ($rows[$i + 1] ?? null)) {
            $offset = 1;
            $allMirrored = true;
            while (($i - $offset) >= 0 && ($i + 1 + $offset) < count($rows)) {
                if ($rows[$i - $offset] !== $rows[$i + 1 + $offset]) {
                    $allMirrored = false;
                }
                $offset++;
            }
            if ($allMirrored) {
                return $i + 1;
            }
        }
    }
    return null;
}

function rotateMatrix90Degrees(array $matrix): array {
    $rows = count($matrix);
    $columns = count($matrix[0]);

    $rotatedMatrix = [];

    for ($col = 0; $col < $columns; $col++) {
        for ($row = $rows - 1; $row >= 0; $row--) {
            $rotatedMatrix[$col][$rows - 1 - $row] = $matrix[$row][$col];
        }
    }

    return $rotatedMatrix;
}

function solve($patterns): int
{
    $sum = 0;
    foreach ($patterns as $i => $pattern) {
        //echo $pattern . PHP_EOL;
        $rows = explode("\n", $pattern);

        $horizontalMatrix = array_map('str_split', $rows);
        $horizontalReflection = findMirrorLocation($horizontalMatrix);

        $verticalMatrix = rotateMatrix90Degrees($horizontalMatrix);
        $verticalReflection = findMirrorLocation($verticalMatrix);

        if ($horizontalReflection) {
            $sum += ($horizontalReflection * 100);
        } else {
            $sum += $verticalReflection;
        }

        //echo $i . ' ' . ($horizontalReflection ?? 0) . ' ' . ($verticalReflection ?? 0) . PHP_EOL;
    }
    return $sum;
}

$solution1 = solve($patterns);
//$solution2 = solve2($patterns);

echo 'Solution 1 = ' . $solution1 . PHP_EOL;
//echo 'Solution 2 = ' . $solution2 . PHP_EOL;