<?php

namespace INTERMediator\Media;

use Exception;
use INTERMediator\DB\Proxy;

interface MediaProcessing
{
    /**
     * Handles file upload processing to Amazon S3.
     * @param Proxy $db The database proxy instance.
     * @param string|null $url The redirect URL on error.
     * @param array<array-key, mixed>|null $options Additional options for processing.
     * @param array<array-key, mixed> $files Uploaded files array.
     * @param bool $noOutput Whether to suppress output.
     * @param array<array-key, mixed> $field Array of target field names.
     * @param string $contextName The context name for processing.
     * @param string|null $keyField The key field for database update.
     * @param string|null $keyValue The key value for database update.
     * @param array<array-key, mixed>|null $dataSource Data source definition.
     * @param array<array-key, mixed>|null $dbSpec Database specification.
     * @param int $debug Debug level.
     * @return void
     * @throws Exception If an error occurs during processing.
     */
    public function processing(Proxy  $db, ?string $url, ?array $options, array $files, bool $noOutput,
                               array  $field, string $contextName, ?string $keyField, ?string $keyValue,
                               ?array $dataSource, ?array $dbSpec, int $debug, ?string $customFileName): void;

}