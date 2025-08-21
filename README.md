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
