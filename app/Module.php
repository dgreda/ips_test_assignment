<?php

declare(strict_types=1);

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Module extends Model
{
    public function tag(): BelongsTo
    {
        return $this->belongsTo('App\Tag');
    }
}
