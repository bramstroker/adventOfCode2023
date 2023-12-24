<?php

enum PulseType
{
    case LOW;
    case HIGH;
}

class Pulse
{
    public function __construct(
        public readonly ModuleInterface $source,
        public readonly ModuleInterface $target,
        public readonly PulseType $type = PulseType::LOW
    ) {
    }

    public function isHigh(): bool
    {
        return $this->type === PulseType::HIGH;
    }

    public function isLow(): bool
    {
        return $this->type === PulseType::LOW;
    }
}

interface ModuleInterface
{
    public function handlePulse(Pulse $pulse): ?PulseType;

    public function addOutputModule(ModuleInterface $module);
}

interface InputAwareInterface
{
    public function addInputModule(ModuleInterface $module);
}

interface StateInterface
{
    public function getState(): bool;
}

class Machine
{
    private array $modules = [];

    private $pulseCounts = [];

    public function __construct()
    {
        $this->pulseCounts[PulseType::LOW->name] = 0;
        $this->pulseCounts[PulseType::HIGH->name] = 0;
    }

    public function addModule(ModuleInterface $module): void
    {
        $this->modules[$module->name] = $module;
    }

    public function getModule(string $name): ?ModuleInterface
    {
        return $this->modules[$name] ?? null;
    }

    public function getModules(): array
    {
        return $this->modules;
    }

    /**
     * @param string $destination
     * @return array
     */
    public function getModulesHavingDestination(string $destination): array
    {
        $destinationModule = $this->getModule($destination);
        return array_filter($this->modules, fn(ModuleInterface $module) => in_array($destinationModule, $module->getOutputModules()));
    }

    public function triggerButton(?callable $pulseCallback = null): void
    {
        $queue = [];

        $this->pulseCounts[PulseType::LOW->name] += 1;
        $broadcasterModule = $this->getModule('broadcaster');
        foreach ($broadcasterModule->getOutputModules() as $outputModule) {
            $queue[] = new Pulse($broadcasterModule, $outputModule, PulseType::LOW);
        }

        /** @var ModuleInterface $module */
        while (($pulse = array_shift($queue)) !== null)
        {
            //echo 'Pulse ' . $pulse->type->name . ' from ' . $pulse->source->name . ' to ' . $pulse->target->name . PHP_EOL;
            $module = $pulse->target;
            $nextPulseType = $module->handlePulse($pulse);
            if ($pulseCallback !== null) {
                $pulseCallback($pulse);
            }

            $this->pulseCounts[$pulse->type->name]++;
            if ($nextPulseType === null) {
                continue;
            }
            foreach ($module->getOutputModules() as $outputModule) {
                $queue[] = new Pulse($module, $outputModule, $nextPulseType);
            }
        }
    }

    public function getPulseCount(PulseType $pulseType): int
    {
        return $this->pulseCounts[$pulseType->name];
    }
}

abstract class AbstractModule implements ModuleInterface
{
    private array $outputModules = [];

    public function __construct(public string $name)
    {
    }

    public function addOutputModule(ModuleInterface $module): void
    {
        $this->outputModules[] = $module;
    }

    public function getOutputModules(): array
    {
        return $this->outputModules;
    }
}

class FlipFlopModule extends AbstractModule implements StateInterface
{
    private bool $state = false;

    public function handlePulse(Pulse $pulse): ?PulseType
    {
        if ($pulse->isHigh()) {
            return null;
        }
        $this->state = !$this->state;
        return $this->state ? PulseType::HIGH : PulseType::LOW;
    }

    public function getState(): bool
    {
        return $this->state;
    }
}

class ConjunctionModule extends AbstractModule implements InputAwareInterface
{
    private array $inputPulses;

    public function addInputModule(ModuleInterface $module): void
    {
        $this->inputPulses[$module->name] = PulseType::LOW;
    }

    public function handlePulse(Pulse $pulse): ?PulseType
    {
        $this->inputPulses[$pulse->source->name] = $pulse->type;
        return (in_array(PulseType::LOW, $this->inputPulses)) ? PulseType::HIGH : PulseType::LOW;
    }
}

class BroadCastModule extends AbstractModule
{
    public function handlePulse(Pulse $pulse): ?PulseType
    {
        return $pulse->type;
    }
}

class NullModule extends AbstractModule
{
    public function handlePulse(Pulse $pulse): ?PulseType
    {
        return null;
    }
}

class ModuleFactory
{
    public function create(string $line): ModuleInterface
    {
        [$module] = explode(' -> ', $line);

        $moduleType = $module;
        $moduleName = $module;
        if (str_starts_with($module, '&') || str_starts_with($module, '%')) {
            $moduleType = substr($module, 0, 1);
            $moduleName = substr($module, 1);
        }

        return match ($moduleType) {
            '%' => new FlipFlopModule($moduleName),
            '&' => new ConjunctionModule($moduleName),
            default => new BroadCastModule($moduleName),
        };
    }
}

class MachineFactory
{
    public function create(array $lines): Machine
    {
        $moduleOutputs = [];
        $moduleFactory = new ModuleFactory();
        $machine = new Machine();
        foreach ($lines as $line) {
            $module = $moduleFactory->create($line);
            [,$destinations] = explode(' -> ', $line);
            $destinations = explode(', ', $destinations);
            $machine->addModule($module);
            $moduleOutputs[$module->name] = $destinations;
            if (in_array('output', $destinations)) {
                $machine->addModule(new NullModule('output'));
            }
        }

        $inputAwareModules = array_filter($machine->getModules(), fn($module) => $module instanceof InputAwareInterface);

        foreach ($moduleOutputs as $moduleName => $destinations) {
            $module = $machine->getModule($moduleName);
            foreach ($destinations as $destination) {
                $outputModule = $machine->getModule($destination);
                if ($outputModule == null) {
                    $outputModule = new NullModule($destination);
                    $machine->addModule($outputModule);
                }
                $module->addOutputModule($outputModule);

                if (in_array($destination, array_keys($inputAwareModules))) {
                    $inputAwareModules[$destination]->addInputModule($module);
                }
            }
        }
        return $machine;
    }
}

$lines = explode("\n", file_get_contents(__DIR__ . '/input3.txt'));

function solve1(array $lines): int
{
    $machineFactory = new MachineFactory();
    $machine = $machineFactory->create($lines);
    for ($i = 0; $i < 1000; $i++) {
        $machine->triggerButton();
    }
    $highCount = $machine->getPulseCount(PulseType::HIGH);
    $lowCount = $machine->getPulseCount(PulseType::LOW);
//    echo 'High: ' . $highCount . PHP_EOL;
//    echo 'Low: ' . $lowCount . PHP_EOL;
    return $lowCount * $highCount . PHP_EOL;
}

function solve2(array $lines): int
{
    $machineFactory = new MachineFactory();
    $machine = $machineFactory->create($lines);
    $rxSourceModule = current($machine->getModulesHavingDestination('rx'));
    $modulesToWatch = $machine->getModulesHavingDestination($rxSourceModule->name);

    $loCycleSteps = [];
    for ($i = 0; $i < 1000000; $i++) {
        $callback = function (Pulse $pulse) use ($modulesToWatch, $i, &$loCycleSteps) {
            if (in_array($pulse->target, $modulesToWatch) && $pulse->isLow()) {
                $loCycleSteps[$pulse->target->name] = $i + 1;
                //echo 'LOW ' . $pulse->target->name . ' at ' . $i . PHP_EOL;
            }
        };

        $machine->triggerButton($callback);
        if (count($loCycleSteps) === count($modulesToWatch)) {
            break;
        }
    }
    return calculateLcm($loCycleSteps);
}

$solution1 = solve1($lines);
$solution2 = solve2($lines);

echo 'Solution 1 = ' . $solution1 . PHP_EOL;
echo 'Solution 2 = ' . $solution2 . PHP_EOL;

function gcd($a, $b): int
{
    if ($b == 0)
        return $a;
    return gcd($b, $a % $b);
}

function calculateLcm($stepSizes): int
{
    $result = 1;
    foreach ($stepSizes as $val)
        $result = ((($val * $result)) / (gcd($val, $result)));
    return $result;
}