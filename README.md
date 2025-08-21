# Laravel Eloquent Generic Rector

## Before (Missing Generic Types)

```php
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends Model
{
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
```

## After (With Generic Types)

```php
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends Model
{
    /**
     * @return BelongsTo<Company, self>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
```

## Installation

```bash
composer require rector-laravel-custom-rules/rules --dev
```

## Configuration

Edit your `rector.php` file:

```php
<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Ofload\LaravelEloquentGenericRector\LaravelEloquentGenericRector;

return RectorConfig::configure()
    ->withRules([
        // Add generic type annotations for Eloquent relations
        LaravelEloquentGenericRector::class,
    ]);
```

## Usage

Run the rector to apply the transformations:

```bash
vendor/bin/rector process
```

## What This Rector Does

This Rector automatically adds generic type annotations to Laravel Eloquent relationship methods, improving type safety and IDE support by adding proper PHPDoc annotations like `@return BelongsTo<Company, self>`.