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

    public function triggerButton(): void
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
            echo 'Pulse ' . $pulse->type->name . ' from ' . $pulse->source->name . ' to ' . $pulse->target->name . PHP_EOL;
            $module = $pulse->target;
            $nextPulseType = $module->handlePulse($pulse);

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
                if ($outputModule !== null) {
                    $module->addOutputModule($machine->getModule($destination));
                } else {
                    $module->addOutputModule(new NullModule($destination));
                }

                if (in_array($destination, array_keys($inputAwareModules))) {
                    $inputAwareModules[$destination]->addInputModule($module);
                }
            }
        }
        return $machine;
    }
}

$lines = explode("\n", file_get_contents(__DIR__ . '/input3.txt'));

$machineFactory = new MachineFactory();
$machine = $machineFactory->create($lines);
for ($i = 0; $i < 1000; $i++) {
    echo 'Trigger ' . $i . PHP_EOL;
    $machine->triggerButton();
}

$highCount = $machine->getPulseCount(PulseType::HIGH) . PHP_EOL;
$lowCount = $machine->getPulseCount(PulseType::LOW) . PHP_EOL;
echo 'High: ' . $highCount . PHP_EOL;
echo 'Low: ' . $lowCount . PHP_EOL;
echo $lowCount * $highCount . PHP_EOL;