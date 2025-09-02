<?php

namespace Rector\Tests\TypeDeclaration\Rector\LaravelEloquentGenericRector\Fixture;

use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Company extends Model
{
    public function users(): HasManyThrough
    {
        return $this->hasManyThrough(User::class, Team::class, 'company_id', 'id', 'id', 'user_id');
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\LaravelEloquentGenericRector\Fixture;

use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Company extends Model
{
    /**
     * @return HasManyThrough<User>
     */
    public function users(): HasManyThrough
    {
        return $this->hasManyThrough(User::class, Team::class, 'company_id', 'id', 'id', 'user_id');
    }
}

?>
