<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Module;
use \DB;

class ModulesRepository
{
    /**
     * @param string $courseKey
     * @param array  $tagIds
     *
     * @return \stdClass|null
     */
    public function getHighestCompletedModuleByCourseKeyAndTagIds(string $courseKey, array $tagIds)
    {
        if (empty($tagIds)) {
            return null;
        }

        $result = DB::table('tags')
            ->join('modules', 'tags.id', '=', 'modules.tag_id')
            ->select('modules.*')
            ->where('modules.course_key', $courseKey)
            ->whereIn('tags.tag_id', $tagIds)
            ->orderBy('modules.name', 'DESC')
            ->first();

        if ($result === null) {
            return null;
        }

        return $result;
    }

    public function getFirstModuleByCourseKey(string $courseKey): Module
    {
        return Module::where('course_key', $courseKey)
            ->orderBy('name', 'ASC')
            ->first();
    }

    public function getModuleByCourseKeyAndNumber(string $courseKey, int $moduleNumber): Module
    {
        return Module::where('course_key', $courseKey)
            ->where('name', strtoupper($courseKey) . ' Module ' . $moduleNumber)
            ->first();
    }
}
