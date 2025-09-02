<?php

namespace Rector\Tests\TypeDeclaration\Rector\LaravelEloquentGenericRector\Fixture;

use Illuminate\Database\Eloquent\Relations\MorphMany;

class User extends Model
{
    public function test(): MorphMany
    {
        return $this->morphMany(PriceAdjustmentHistory::class, 'recordable');
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\LaravelEloquentGenericRector\Fixture;

use Illuminate\Database\Eloquent\Relations\MorphMany;

class User extends Model
{
    /**
     * @return MorphMany<PriceAdjustmentHistory>
     */
    public function test(): MorphMany
    {
        return $this->morphMany(PriceAdjustmentHistory::class, 'recordable');
    }
}

?>
