<?php

declare(strict_types=1);

namespace App\Services;

use App\Module;
use App\Tag;
use \DB;
use RuntimeException;

class ModuleReminderAssigner
{
    /**
     * @var InfusionsoftClient
     */
    private $infusionsoftClient;

    /**
     * @param InfusionsoftClient $infusionsoftClient
     */
    public function __construct(InfusionsoftClientInterface $infusionsoftClient)
    {
        $this->infusionsoftClient = $infusionsoftClient;
    }

    public function assignReminderTag(string $contactEmail): array
    {
        $contact = $this->infusionsoftClient->getContact($contactEmail);
        if (null === $contact) {
            throw new RuntimeException('Contact ' . $contactEmail . ' not found!');
        }
        $moduleToAssign = null;
        $courseKeys     = $this->getCourseKeys($contact);
        $tagIds         = $this->getTagIds($contact);

        foreach ($courseKeys as $courseKey) {
            $highestCompletedModuleInCourse = $this->getHighestCompletedModuleByCourseKeyAndTagIds($courseKey, $tagIds);

            if ($highestCompletedModuleInCourse === null) {
                $moduleToAssign = $this->getFirstModuleByCourseKey($courseKey);
                break;
            }

            $highestCompletedModuleName      = $highestCompletedModuleInCourse->name;
            $highestCompletedModuleNameParts = explode(' ', $highestCompletedModuleName);
            $highestCompletedModuleNumber    = (int) array_pop($highestCompletedModuleNameParts);
            if ($highestCompletedModuleNumber < 7) {
                $moduleToAssign = $this->getModuleByCourseKeyAndNumber(
                    $courseKey,
                    $highestCompletedModuleNumber + 1
                );
                break;
            }
        }

        if ($moduleToAssign === null) {
            $result = $this->infusionsoftClient->addTag($contact['Id'], $this->getFinalTag()->tag_id);

            return $this->returnResult($result, "Adding final tag 'Module reminders completed'");
        }

        $result = $this->infusionsoftClient->addTag($contact['Id'], $moduleToAssign->tag->tag_id);

        return $this->returnResult($result, "Adding tag '" . $moduleToAssign->tag->name . "'");
    }

    private function returnResult($result, $messagePrefix): array
    {
        return [
            $result,
            $messagePrefix . " " . ($result ? 'succeeded' : 'failed'),
        ];
    }

    private function getCourseKeys(array $contact): array
    {
        $products = [];

        if (!empty($contact['_Products'])) {
            $products = explode(',', (string) $contact['_Products']);
        }

        return $products;
    }

    private function getTagIds(array $contact): array
    {
        $tagIds = [];

        if (!empty($contact['Groups'])) {
            $tagIds = explode(',', (string) $contact['Groups']);
        }

        return $tagIds;
    }

    /**
     * @param string $courseKey
     * @param array  $tagIds
     *
     * @return \stdClass|null
     */
    private function getHighestCompletedModuleByCourseKeyAndTagIds(string $courseKey, array $tagIds)
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

    private function getFirstModuleByCourseKey(string $courseKey): Module
    {
        return Module::where('course_key', $courseKey)
            ->orderBy('name', 'ASC')
            ->first();
    }

    private function getModuleByCourseKeyAndNumber(string $courseKey, int $moduleNumber): Module
    {
        return Module::where('course_key', $courseKey)
            ->where('name', strtoupper($courseKey) . ' Module ' . $moduleNumber)
            ->first();
    }

    private function getFinalTag(): Tag
    {
        return Tag::where('name', 'Module reminders completed')->first();
    }
}