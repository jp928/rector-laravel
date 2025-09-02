<?php

namespace Rector\Tests\TypeDeclaration\Rector\LaravelEloquentGenericRector\Fixture;

use Illuminate\Database\Eloquent\Relations\MorphTo;

class User extends Model
{
    public function adjustable(): MorphTo
    {
        return $this->morphTo();
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\LaravelEloquentGenericRector\Fixture;

use Illuminate\Database\Eloquent\Relations\MorphTo;

class User extends Model
{
    /**
     * @return MorphTo<Model, self>
     */
    public function adjustable(): MorphTo
    {
        return $this->morphTo();
    }
}

?>
