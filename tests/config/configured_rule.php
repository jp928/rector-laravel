<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use RectorLaravel\Rules\LaravelEloquentGenericRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(LaravelEloquentGenericRector::class);
};
