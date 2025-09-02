<?php

namespace Rector\Tests\TypeDeclaration\Rector\LaravelEloquentGenericRector\Fixture;

use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class User extends Model
{
    public function company(): HasOneThrough
    {
        // Return a HasOne relationship representing the first company from companies()
        return $this->hasOneThrough(
            Company::class,
            Team::class,
            'user_id',
            'id',
            'id',
            'company_id'
        )
            ->orderBy('team.created_at')
            ->limit(1);
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\LaravelEloquentGenericRector\Fixture;

use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class User extends Model
{
    /**
     * @return HasOneThrough<Company>
     */
    public function company(): HasOneThrough
    {
        // Return a HasOne relationship representing the first company from companies()
        return $this->hasOneThrough(
            Company::class,
            Team::class,
            'user_id',
            'id',
            'id',
            'company_id'
        )
            ->orderBy('team.created_at')
            ->limit(1);
    }
}

?>
