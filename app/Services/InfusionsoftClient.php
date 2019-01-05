<?php

declare(strict_types=1);

namespace App\Services;

use Infusionsoft;
use Infusionsoft\InfusionsoftCollection;
use Log;
use Storage;
use Request;

class InfusionsoftClient implements InfusionsoftClientInterface
{
    public function __construct()
    {
        if (Storage::exists('inf_token')) {
            Infusionsoft::setToken(unserialize(Storage::get("inf_token")));
        } else {
            Log::error("Infusionsoft token not set.");
        }
    }

    public function authorize(): string
    {
        if (Request::has('code')) {
            Infusionsoft::requestAccessToken(Request::get('code'));

            Storage::put('inf_token', serialize(Infusionsoft::getToken()));
            Log::notice('Infusionsoft token created');

            Infusionsoft::setToken(unserialize(Storage::get("inf_token")));

            return 'Success';
        }

        return '<a href="' . Infusionsoft::getAuthorizationUrl() . '">Authorize Infusionsoft</a>';
    }

    public function getAllTags(): InfusionsoftCollection
    {
        return Infusionsoft::tags()->all();
    }

    public function getContact(string $email): ?array
    {
        $fields = [
            'Id',
            'Email',
            'Groups',
            "_Products",
        ];

        return Infusionsoft::contacts('xml')->findByEmail($email, $fields)[0];
    }

    public function addTag(int $contactId, int $tagId): bool
    {
        return Infusionsoft::contacts('xml')->addToGroup($contactId, $tagId);
    }

    public function createContact(array $data)
    {
        try {
            return Infusionsoft::contacts('xml')->add($data);
        } catch (\Exception $e) {
            Log::error((string) $e);

            return false;
        }
    }
}
