<?php
$rows = explode("\n", file_get_contents(__DIR__ . '/input2.txt'));

function numArrangements(string $springs, array $groups, $index = 0, $groupIndex = 0, $groupLength = 0, &$cache = []): int
{
    if (isset($cache[$index][$groupIndex][$groupLength])) {
        return $cache[$index][$groupIndex][$groupLength];
    }

    if ($index == strlen($springs))
    {
        if ($groupIndex == count($groups) - 1 && $groupLength == $groups[$groupIndex]) {
            $groupIndex++;
            $groupLength = 0;
        }
        return (int)($groupIndex == count($groups) && $groupLength == 0);
    }

    $result = 0;

    if (in_array($springs[$index], ['.', '?']))
    {
        if ($groupLength == 0) {
            $result += numArrangements($springs, $groups, $index + 1, $groupIndex, 0, $cache);
        } else if ($groupIndex < count($groups) && $groups[$groupIndex] == $groupLength) {
            $result += numArrangements($springs, $groups, $index + 1, $groupIndex + 1, 0, $cache);
        }
    }

    if (in_array($springs[$index], ['#', '?']))
    {
        $result += numArrangements($springs, $groups, $index + 1, $groupIndex, $groupLength + 1, $cache);
    }

    $cache[$index][$groupIndex][$groupLength] = $result;
    return $result;
}

function solve(array $rows): int
{
    $sum = 0;
    foreach ($rows as $row) {
        $parts = explode(' ', $row);
        $springs = $parts[0];
        $groups = explode(',', $parts[1]);

        $count = numArrangements($springs, $groups);
        $sum += $count;
    }
    return $sum;
}

function solve2(array $rows): int
{
    $sum = 0;
    foreach ($rows as $row) {
        $parts = explode(' ', $row);
        $springs = $parts[0];
        $groups = explode(',', $parts[1]);

        $expandedSprings = '';
        $expandedGroups = [];
        for ($i = 0; $i < 5; $i++) {
            if ($i > 0) {
                $expandedSprings .= '?';
            }
            $expandedSprings .= $springs;
            $expandedGroups = [...$expandedGroups, ...$groups];
        }

        $count = numArrangements($expandedSprings, $expandedGroups);
        $sum += $count;
    }
    return $sum;
}

$solution1 = solve($rows);
$solution2 = solve2($rows);

echo 'Solution 1 = ' . $solution1 . PHP_EOL;
echo 'Solution 2 = ' . $solution2 . PHP_EOL;