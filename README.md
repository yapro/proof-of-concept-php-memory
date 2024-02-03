# proof-of-concept-php-memory

Important information:
- peak memory usage cannot decrease
- only memory_get_peak_usage(true) returns the true amount of memory consumed
- global variables require special attention

See below for details.

If you run the script:
```shell
docker run -it --rm -v "$PWD":/app -w /app php:7.4-cli php -d memory_limit=-1 index.php
```

You got the result:
```shell
memory_get_usage(false)         memory_get_usage(true)          memory_get_peak_usage(false)    memory_get_peak_usage(true)
-- a function can use memory even if the result is not used: ------------------------------------------------------------------------
1                               2                               53                              55
-- gc_collect_cycles has no effect on memory usage: ---------------------------------------------------------------------------------
1                               2                               53                              55
-- creating a local variable increases memory usage: --------------------------------------------------------------------------------
53                              55                              53                              55
-- the local variable is destroyed and the memory is freed without gc_collect_cycles: -----------------------------------------------
1                               2                               53                              55
-- creating a global variable increases memory usage: -------------------------------------------------------------------------------
53                              55                              53                              55
-- gc_collect_cycles has no effect on memory usage because the global variable exists: ----------------------------------------------
53                              55                              53                              55
-- a function may increase memory usage even if the result is not used: -------------------------------------------------------------
53                              55                              106                             107
-- the global variable is destroyed and the memory is freed without gc_collect_cycles: ----------------------------------------------
1                               2                               106                             107
-- :::::::::::::::::::::::Doctrine EntityManager:::::::::::::::::::: ----------------------------------------------------------------
1                               2                               106                             107
-- EntityManager does not consume memory by default: --------------------------------------------------------------------------------
1                               2                               106                             107
-- creating a local variable increases memory usage: --------------------------------------------------------------------------------
53                              55                              106                             107
-- adding local variable does not increase memory usage: ----------------------------------------------------------------------------
53                              55                              106                             107
-- but after the local variable was destroyed, the memory is not freed, because the local variable is part of the global variable: --
53                              55                              106                             107
-- and gc_collect_cycles has no effect on memory usage, because the local variable is part of the global variable: ------------------
53                              55                              106                             107
-- global variable is decremented and memory is freed without gc_collect_cycles - php is smart: -------------------------------------
1                               2                               106                             107
```

Detailed information about references:
- https://rmcreative.ru/blog/post/utechki-pamjati-v-php
- https://www.php.net/manual/ru/features.gc.php
- need to test: https://www.php.net/manual/ru/class.weakmap.php
