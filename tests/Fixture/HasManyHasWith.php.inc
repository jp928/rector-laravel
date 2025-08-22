<?php

namespace Rector\Tests\TypeDeclaration\Rector\LaravelEloquentGenericRector\Fixture;

use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class, 'company_id', 'id')->with('team');
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\LaravelEloquentGenericRector\Fixture;

use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    /**
     * @return HasMany<Employee>
     */
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class, 'company_id', 'id')->with('team');
    }
}

?>
