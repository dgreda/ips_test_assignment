<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\ModulesRepository;
use App\Repositories\TagsRepository;
use App\Tag;
use RuntimeException;

class ModuleReminderAssigner
{
    private const MAX_NUMBER_OF_MODULES = 7;

    /**
     * @var InfusionsoftClient
     */
    private $infusionsoftClient;

    /**
     * @var TagsRepository
     */
    private $tagsRepository;

    /**
     * @var ModulesRepository
     */
    private $modulesRepository;

    public function __construct(
        InfusionsoftClientInterface $infusionsoftClient,
        TagsRepository $tagsRepository,
        ModulesRepository $modulesRepository
    ) {
        $this->infusionsoftClient = $infusionsoftClient;
        $this->tagsRepository     = $tagsRepository;
        $this->modulesRepository  = $modulesRepository;
    }

    public function assignReminderTag(string $contactEmail): array
    {
        $moduleToAssign = null;
        $finalTag       = $this->tagsRepository->getFinalTag();
        $contact        = $this->infusionsoftClient->getContact($contactEmail);

        if (null === $contact) {
            throw new RuntimeException('Contact ' . $contactEmail . ' not found!');
        }

        if ($this->hasFinalTag($contact, $finalTag)) {
            return $this->returnResult(
                false,
                "Final tag 'Module reminders completed' already attached. Request"
            );
        }

        $moduleToAssign = $this->findModuleToAssign($contact);

        if ($moduleToAssign === null) {
            $result = $this->infusionsoftClient->addTag($contact['Id'], $finalTag->tag_id);

            return $this->returnResult($result, "Adding final tag 'Module reminders completed'");
        }

        $result = $this->infusionsoftClient->addTag($contact['Id'], $moduleToAssign->tag->tag_id);

        return $this->returnResult($result, "Adding tag '" . $moduleToAssign->tag->name . "'");
    }

    private function findModuleToAssign(array $contact)
    {
        $moduleToAssign = null;
        $courseKeys     = $this->extractCourseKeys($contact);
        $tagIds         = $this->extractTagIds($contact);

        foreach ($courseKeys as $courseKey) {
            $highestCompletedModuleInCourse = $this->modulesRepository->getHighestCompletedModuleByCourseKeyAndTagIds(
                $courseKey,
                $tagIds
            );

            if ($highestCompletedModuleInCourse === null) {
                $moduleToAssign = $this->modulesRepository->getFirstModuleByCourseKey($courseKey);
                break;
            }

            $highestCompletedModuleNumber = $this->getHighestCompletedModuleNumber($highestCompletedModuleInCourse);

            if ($highestCompletedModuleNumber < self::MAX_NUMBER_OF_MODULES) {
                $moduleToAssign = $this->modulesRepository->getModuleByCourseKeyAndNumber(
                    $courseKey,
                    $highestCompletedModuleNumber + 1
                );
                break;
            }
        }

        return $moduleToAssign;
    }

    private function getHighestCompletedModuleNumber($highestCompletedModuleInCourse): int
    {
        $highestCompletedModuleNameParts = explode(' ', $highestCompletedModuleInCourse->name);

        return (int) array_pop($highestCompletedModuleNameParts);
    }

    private function returnResult($result, $messagePrefix): array
    {
        return [
            $result,
            $messagePrefix . " " . ($result ? 'succeeded' : 'failed'),
        ];
    }

    private function extractCourseKeys(array $contact): array
    {
        $products = [];

        if (!empty($contact['_Products'])) {
            $products = explode(',', (string) $contact['_Products']);
        }

        return $products;
    }

    private function extractTagIds(array $contact): array
    {
        $tagIds = [];

        if (!empty($contact['Groups'])) {
            $tagIds = explode(',', (string) $contact['Groups']);
        }

        return $tagIds;
    }

    private function hasFinalTag(array $contact, Tag $finalTag): bool
    {
        $tagIds = $this->extractTagIds($contact);

        return in_array($finalTag->tag_id, $tagIds);
    }
}