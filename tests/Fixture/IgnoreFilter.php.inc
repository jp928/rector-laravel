<?php

namespace Rector\Tests\TypeDeclaration\Rector\LaravelEloquentGenericRector\Fixture;

use Illuminate\Database\Eloquent\Relations\HasOne;

class Company extends Model
{
    public function employees(): HasOne
    {
        return $this->filter(UserType::SHIPPER, ArrivalType::PICKUP, false);
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\LaravelEloquentGenericRector\Fixture;

use Illuminate\Database\Eloquent\Relations\HasOne;

class Company extends Model
{
    public function employees(): HasOne
    {
        return $this->filter(UserType::SHIPPER, ArrivalType::PICKUP, false);
    }
}

?>
