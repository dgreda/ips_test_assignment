<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\InfusionsoftClient;
use Illuminate\Http\JsonResponse;
use Response;

class InfusionsoftController extends Controller
{
    /**
     * @var InfusionsoftClient
     */
    private $infusionsoftClient;

    /**
     * @param InfusionsoftClient $infusionsoftClient
     */
    public function __construct(InfusionsoftClient $infusionsoftClient)
    {
        $this->infusionsoftClient = $infusionsoftClient;
    }

    public function authorizeInfusionsoft()
    {
        return $this->infusionsoftClient->authorize();
    }

    public function testInfusionsoftIntegrationGetEmail(string $email): JsonResponse
    {
        return Response::json($this->infusionsoftClient->getContact($email));
    }

    public function testInfusionsoftIntegrationAddTag(int $contactId, int $tagId): JsonResponse
    {
        return Response::json($this->infusionsoftClient->addTag($contactId, $tagId));
    }

    public function testInfusionsoftIntegrationGetAllTags(): JsonResponse
    {
        return Response::json($this->infusionsoftClient->getAllTags());
    }

    public function testInfusionsoftIntegrationCreateContact(): JsonResponse
    {
        return Response::json($this->infusionsoftClient->createContact([
            'Email'     => uniqid() . '@test.com',
            "_Products" => 'ipa,iea',
        ]));
    }
}
