<?php

declare(strict_types=1);

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Tag extends Model
{
    public function module(): HasOne
    {
        return $this->hasOne('App\Module');
    }
}
