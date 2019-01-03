<?php

declare(strict_types=1);

namespace App\Services;

use Infusionsoft;
use Infusionsoft\InfusionsoftCollection;
use Log;
use Storage;
use Request;

class InfusionsoftClient
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

    public function getAllTags(): ?InfusionsoftCollection
    {
        try {
            return Infusionsoft::tags()->all();
        } catch (\Exception $e) {
            Log::error((string) $e);

            return null;
        }
    }

    public function getContact(string $email): ?array
    {
        $fields = [
            'Id',
            'Email',
            'Groups',
            "_Products",
        ];

        try {
            return Infusionsoft::contacts('xml')->findByEmail($email, $fields)[0];
        } catch (\Exception $e) {
            Log::error((string) $e);

            return null;
        }
    }

    public function addTag(string $contact_id, string $tag_id): bool
    {
        try {
            return Infusionsoft::contacts('xml')->addToGroup($contact_id, $tag_id);
        } catch (\Exception $e) {
            Log::error((string) $e);

            return false;
        }
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
