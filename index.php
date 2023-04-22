<?php

declare(strict_types=1);

function printInfo(string $info): void
{
    echo '-- ' . str_pad($info . ': ', 130, '-') . PHP_EOL .
        ceil(memory_get_usage(false) / 1024 / 1024) . "\t\t\t\t" .
        ceil(memory_get_usage(true) / 1024 / 1024) . "\t\t\t\t" .
        ceil(memory_get_peak_usage(false) / 1024 / 1024) . "\t\t\t\t" .
        ceil(memory_get_peak_usage(true) / 1024 / 1024) . PHP_EOL;
}

echo
    'memory_get_usage(false)' . "\t\t" .
    'memory_get_usage(true)' . "\t\t" .
    'memory_get_peak_usage(false)' . "\t" .
    'memory_get_peak_usage(true)' . PHP_EOL;

str_repeat("Hello world", 5000000);
printInfo('a function can use memory even if the result is not used');

gc_collect_cycles();
printInfo('gc_collect_cycles has no effect on memory usage');

(static function () {
    $result = str_repeat("Hello world", 5000000);
    printInfo('creating a local variable increases memory usage');
})();
printInfo('the local variable is destroyed and the memory is freed without gc_collect_cycles');

$result = str_repeat("Hello world", 5000000);
printInfo('creating a global variable increases memory usage');

gc_collect_cycles();
printInfo('gc_collect_cycles has no effect on memory usage because the global variable exists');

str_repeat("Hello world", 5000000);
printInfo('a function may increase memory usage even if the result is not used');

unset($result);
printInfo('the global variable is destroyed and the memory is freed without gc_collect_cycles');

//---------------------------------- PART 2 ----------------------------------------

printInfo(':::::::::::::::::::::::Doctrine EntityManager:::::::::::::::::::');

class EntityManager
{
    private array $unitOfWork = [];

    function persist($entity): void
    {
        $this->unitOfWork[] = $entity;
    }

    function clear(): void
    {
        $this->unitOfWork = [];
    }
}

$em = new EntityManager();
printInfo('EntityManager does not consume memory by default');

(static function () use ($em) {
    $entity = new ArrayObject([str_repeat("Hello world", 5000000)]);
    printInfo('creating a local variable increases memory usage');
    $em->persist($entity);
    printInfo('adding local variable does not increase memory usage');
})();
printInfo(
    'but after the local variable was destroyed, the memory is not freed, because the local variable is part of the global variable'
);

gc_collect_cycles();
printInfo(
    'and gc_collect_cycles has no effect on memory usage, because the local variable is part of the global variable'
);

$em->clear();
printInfo('global variable is decremented and memory is freed without gc_collect_cycles - php is smart');
