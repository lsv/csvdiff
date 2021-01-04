<?php
require __DIR__ . '/vendor/autoload.php';

use Lsv\CsvDiff\Command\CsvDiffCommand;
use Symfony\Component\Console\Application;

$app = new Application('CSV Diff', '1.0');
$command = new CsvDiffCommand();
$app->add($command);
$app->setDefaultCommand((string)$command->getName(), true);
$app->run();
