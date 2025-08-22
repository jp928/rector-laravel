<?php

namespace Rector\Tests\TypeDeclaration\Rector\LaravelEloquentGenericRector\Fixture;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends Model
{
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'created_by')->with('team')->withTrashed();
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\LaravelEloquentGenericRector\Fixture;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends Model
{
    /**
     * @return BelongsTo<Company, self>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'created_by')->with('team')->withTrashed();
    }
}

?>
