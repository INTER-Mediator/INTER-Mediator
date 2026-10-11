<?php

namespace INTERMediator\DB\Support;

use Exception;

interface ProtocolProcessing
{
    /** Processes export of context data to CSV and outputs the file to the browser.
     * @param array<array<float|int|string|null>> $contextData The data to export.
     * @param array<array<string, number|string|bool|null>>|null $options Optional export options.
     * @return void
     * @throws Exception
     */
    public function processing(array $contextData, ?array $options): void;

}