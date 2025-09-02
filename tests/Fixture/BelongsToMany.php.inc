<?php

namespace Rector\Tests\TypeDeclaration\Rector\LaravelEloquentGenericRector\Fixture;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Model
{
    public function company(): BelongsToMany
    {
        return $this->belongsToMany(
            OperationsMatrix::class,
            'operations_matrix_filter',
            'operation_filter_id',
            'operations_matrix_id'
        );
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\LaravelEloquentGenericRector\Fixture;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Model
{
    /**
     * @return BelongsToMany<OperationsMatrix>
     */
    public function company(): BelongsToMany
    {
        return $this->belongsToMany(
            OperationsMatrix::class,
            'operations_matrix_filter',
            'operation_filter_id',
            'operations_matrix_id'
        );
    }
}

?>
