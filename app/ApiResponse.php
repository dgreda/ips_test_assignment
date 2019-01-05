<?php

declare(strict_types=1);

namespace App;

use Illuminate\Database\Eloquent\Model;

class ApiResponse extends Model
{
    /**
     * The attributes that should be visible in arrays.
     *
     * @var array
     */
    protected $visible = ['success', 'message'];
}
