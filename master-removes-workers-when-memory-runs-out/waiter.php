<?php
// есть гипотеза, что если памяти не хватает, то phpfpm-мастер процесс прибивает дочерние

$id = uniqid('', false);
function writeln ($message = '') {
    global $id;
    file_put_contents(__DIR__ . '/result.txt', $id . "\t" . microtime(true) . "\t" . $message . PHP_EOL, FILE_APPEND);
}

$statusInfo = 'xx';//file_get_contents('http://nginx:80/fpm_status');

writeln($statusInfo ? str_replace("\n", "\t", $statusInfo) : "no");

str_repeat("Hello world", 5000000);

writeln();

var_dump(ceil(memory_get_usage() / 1024 / 1024));
var_dump(ceil(memory_get_peak_usage(true) / 1024 / 1024));
