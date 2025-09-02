<?php

namespace Rector\Tests\TypeDeclaration\Rector\LaravelEloquentGenericRector\Fixture;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends Model
{
    public function originalShipment(): BelongsTo
    {
        return $this->belongsTo(static::class, 'original_shipment_id');
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
     * @return BelongsTo<self, self>
     */
    public function originalShipment(): BelongsTo
    {
        return $this->belongsTo(static::class, 'original_shipment_id');
    }
}

?>
