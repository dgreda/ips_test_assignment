<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Tag;

class TagsRepository
{
    public function getFinalTag(): Tag
    {
        return Tag::where('name', 'Module reminders completed')->firstOrFail();
    }
}
