<?php

namespace INTERMediator;

use Exception;

require_once('./INTER-Mediator.php');

try {
    [$yamlContent, $defFile] = IMUtil::getYAMLDefContent();
} catch (Exception $ex) {
    header("Content-Type: text/html");
    echo "<script>console.error('{$ex->getMessage()}')\nwindow.alert('{$ex->getMessage()}')</script>\n\n";
    exit;
}
IM_Entry(
    $yamlContent['contexts'] ?? null, $yamlContent['options'] ?? null,
    $yamlContent['connection'] ?? null, $yamlContent['debug'] ?? 2, $defFile
);
