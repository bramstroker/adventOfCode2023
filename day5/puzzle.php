<?php
$lines = explode("\n", file_get_contents(__DIR__ . '/input.txt'));

# Remove empty lines
$lines = array_values(array_filter($lines));

function parseMappings($lines): array
{
    $mappings = [];
    for ($i = 1; $i < count($lines); $i++) {
        $line = $lines[$i];
        if (preg_match('/(.*)-to-(.*) map/', $line, $matches)) {
            $mapping = new Mapping($matches[1], $matches[2]);
            $mappings[] = $mapping;
            continue;
        }

        $map = explode(' ', $line);
        $mappingLine = new MappingLine((float) $map[0], (float) $map[1], (float) $map[2]);
        $mapping->addMappingLine($mappingLine);
    }

    foreach ($mappings as $mapping) {
        usort($mapping->mappings, function ($a, $b) {
            return $a->destinationStart <=> $b->destinationStart;
        });
    }

    return $mappings;
}

function solve1($seedLine, RouteCalculator $calculator): float
{
    $seeds = explode(' ', substr($seedLine, strpos($seedLine, ':') + 2));
    $locations = [];
    foreach ($seeds as $i => $seed) {
        $locations[] = $calculator->getLocationForSeed($seed);
    }

    return min($locations);
}

function solve2($seedLine, RouteCalculator $calculator): float
{
    $parts = explode(' ', substr($seedLine, strpos($seedLine, ':') + 2));

    $ranges = [];
    for ($i = 0; $i < count($parts); $i++) {
        $ranges[] = [$parts[$i], $parts[$i+1]];
        $i += 1;
    }

    [$seed, $location] = $calculator->findFirstPossibleSeedLocation($ranges, 0, 1000);
    echo 'Found seed ' . $seed . ' at location ' . $location . PHP_EOL;
    [$seed, $location] = $calculator->findFirstPossibleSeedLocation($ranges, $location - 10000);
    echo 'Found seed ' . $seed . ' at location ' . $location . PHP_EOL;

    return $location;
}

class RouteCalculator
{
    public function __construct(private readonly array $mappings)
    {
    }

    function getLocationForSeed(float $seed): float
    {
        $search = $seed;
        /** @var Mapping $mapping */
        foreach ($this->mappings as $i => $mapping) {
            foreach ($mapping->mappings as $map2) {
                $destination = $map2->getDestination($search);
                if ($destination) {
                    //echo 'Found ' . $search . ' -> ' . $destination . PHP_EOL;
                    $search = $destination;
                    break;
                }
            }
        }

        return $search;
    }

    function getSeedForLocation(float $location): float
    {
        $search = $location;
        /** @var Mapping $mapping */
        foreach (array_reverse($this->mappings) as $mapping) {
            foreach ($mapping->mappings as $map2) {
                $destination = $map2->getSource($search);
                if ($destination) {
                    $search = $destination;
                    break;
                }
            }
        }

        return $search;
    }

    public function findFirstPossibleSeedLocation(array $seedRanges, $startLocation = 0, int $resolution = 1): array
    {
        $location = $startLocation;
        while (true) {
            //echo 'Try location ' . $location . PHP_EOL;
            $seed = $this->getSeedForLocation($location);
            $location += $resolution;
            //echo 'Seed ' . $seed . PHP_EOL;
            foreach ($seedRanges as $range) {
                if ($seed >= $range[0] && $seed <= $range[0] + $range[1]) {
                    return [$seed, $location-1];
                }
            }
        }
    }
}

class Mapping
{
    public function __construct(
        public string $sourceType,
        public string $destinationType,
        public array $mappings = [],
    ) {}

    public function addMappingLine(MappingLine $mappingLine): void
    {
        $this->mappings[] = $mappingLine;
    }
}

class MappingLine
{
    private float $sourceEnd;
    private float $destinationEnd;

    public function __construct(
        public float $destinationStart,
        public float $sourceStart,
        public float $range,
    ) {
        $this->sourceEnd = $this->sourceStart + $this->range;
        $this->destinationEnd = $this->destinationStart + $this->range - 1;
    }

    public function getDestination(float $source): ?float
    {
        if ($source < $this->sourceStart || $source > $this->sourceEnd) {
            return null;
        }

        return $this->destinationStart + ($source - $this->sourceStart);
    }

    public function getSource(float $destination): ?float
    {
        if ($destination < $this->destinationStart || $destination > $this->destinationEnd) {
            return null;
        }

        return $this->sourceStart + ($destination - $this->destinationStart);
    }
}




$mappings = parseMappings($lines);
$routeCalculator = new RouteCalculator($mappings);

$solution1 = solve1($lines[0], $routeCalculator);
$solution2 = solve2($lines[0], $routeCalculator);

echo 'Solution 1 = ' . $solution1 . PHP_EOL;
echo 'Solution 2 = ' . $solution2 . PHP_EOL;