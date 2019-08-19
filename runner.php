<?php

include_once 'ComposerOrphanChecker.php';

$jsonFileName = $argv[1];
$lockFileName = $argv[2];

$checker = new ComposerOrphanChecker($jsonFileName, $lockFileName);
var_export($checker->getOrphans());
