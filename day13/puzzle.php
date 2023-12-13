<?php
$patterns = explode("\n\n", file_get_contents(__DIR__ . '/input2.txt'));

function findMirrorLocation(array $rows, bool $needsSmudge = false): int
{
    $numRows = count($rows);
    for ($i = 0; $i < $numRows - 1; $i++) {
        $diffCount = count(array_diff_assoc($rows[$i], $rows[$i + 1]));
        if ($diffCount > ($needsSmudge ? 1 : 0)) {
            continue;
        }

        if (areRowsMirrored($rows, $i, $needsSmudge)) {
            return $i + 1;
        }
    }

    return 0;
}

function areRowsMirrored(array $rows, int $index, bool $needsSmudge): bool
{
    $offset = 0;
    $allMirrored = true;
    $smudgeCount = 0;
    $numRows = count($rows);

    # Compare rows above and below the current row until we hit boundaries or a non-mirror
    while (($index - $offset) >= 0 && ($index + 1 + $offset) < $numRows) {
        $diffCount = count(array_diff_assoc($rows[$index - $offset], $rows[$index + 1 + $offset]));
        $offset++;
        if (!$diffCount) {
            continue;
        }

        $smudgeCount += $diffCount;
        if ($needsSmudge && $smudgeCount > 1 || !$needsSmudge) {
            $allMirrored = false;
            break;
        }
    }

    return $needsSmudge ? $smudgeCount === 1 && $allMirrored : $allMirrored;
}

function rotateMatrix90Degrees(array $matrix): array
{
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

function solve($patterns, $allowErrors = false): int
{
    $sum = 0;
    foreach ($patterns as $i => $pattern) {
        //echo $pattern . PHP_EOL;
        $rows = explode("\n", $pattern);

        $horizontalMatrix = array_map('str_split', $rows);
        $horizontalReflection = findMirrorLocation($horizontalMatrix, $allowErrors);
        $verticalReflection = 0;
        if ($horizontalReflection === 0) {
            $verticalMatrix = rotateMatrix90Degrees($horizontalMatrix);
            $verticalReflection = findMirrorLocation($verticalMatrix, $allowErrors);
        }

        $sum += ($horizontalReflection * 100) + $verticalReflection;

        //echo $i . ' ' . $horizontalReflection. ' ' . $verticalReflection . PHP_EOL;
    }
    return $sum;
}

$solution1 = solve($patterns);
$solution2 = solve($patterns, true);

echo 'Solution 1 = ' . $solution1 . PHP_EOL;
echo 'Solution 2 = ' . $solution2 . PHP_EOL;