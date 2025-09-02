<?php

namespace Rector\Tests\TypeDeclaration\Rector\LaravelEloquentGenericRector\Fixture;

use Illuminate\Database\Eloquent\Relations\MorphOne;

class User extends Model
{
    public function callCentre(): MorphOne
    {
        return $this->morphOne(CallCentre::class, 'callcentrable');
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\LaravelEloquentGenericRector\Fixture;

use Illuminate\Database\Eloquent\Relations\MorphOne;

class User extends Model
{
    /**
     * @return MorphOne<CallCentre>
     */
    public function callCentre(): MorphOne
    {
        return $this->morphOne(CallCentre::class, 'callcentrable');
    }
}

?>
