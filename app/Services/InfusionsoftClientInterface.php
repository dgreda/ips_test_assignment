<?php

declare(strict_types=1);

namespace App\Services;

use Infusionsoft\InfusionsoftCollection;

interface InfusionsoftClientInterface
{
    public function authorize(): string;

    public function getAllTags(): InfusionsoftCollection;

    public function getContact(string $email): ?array;

    public function addTag(int $contactId, int $tagId): bool;

    public function createContact(array $data);
}
