<?php

namespace INTERMediator;

use Exception;

require_once('./INTER-Mediator.php');

try {
    $yamlContent = IMUtil::getYAMLDefContent();
} catch (Exception $ex) {
    echo "console.error('{$ex->getMessage()}')\nwindow.alert('{$ex->getMessage()}')\n\n";
    exit;
}
IM_Entry(
    $yamlContent['contexts'] ?? null,
    $yamlContent['options'] ?? null,
    $yamlContent['connection'] ?? null,
    $yamlContent['debug'] ?? 2
);
