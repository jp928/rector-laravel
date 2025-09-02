<?php

declare(strict_types=1);

use RectorLaravelCustomRules\Rules\LaravelEloquentGenericRector;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(LaravelEloquentGenericRector::class);
};
