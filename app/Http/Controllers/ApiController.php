<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\ApiResponse;
use App\Services\InfusionsoftClient;
use App\Services\ModuleReminderAssigner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ApiController extends Controller
{
    /**
     * @var ModuleReminderAssigner
     */
    private $moduleReminderAssigner;

    /**
     * @param ModuleReminderAssigner $moduleReminderAssigner
     */
    public function __construct(ModuleReminderAssigner $moduleReminderAssigner)
    {
        $this->moduleReminderAssigner = $moduleReminderAssigner;
    }

    public function assignReminderTag(Request $request): JsonResponse
    {
        $contactEmail = $request->get('contact_email');

        if (null === $contactEmail) {
            throw new BadRequestHttpException();
        }

        list($result, $message) = $this->moduleReminderAssigner->assignReminderTag($contactEmail);

        $apiResponse          = new ApiResponse;
        $apiResponse->success = $result;
        $apiResponse->message = $message;

        return new JsonResponse(
            $apiResponse,
            $result ? SymfonyResponse::HTTP_CREATED : SymfonyResponse::HTTP_UNPROCESSABLE_ENTITY
        );
    }

    /*private function exampleCustomer()
    {
        $infusionsoft = new InfusionsoftClient();

        $uniqid = uniqid();

        $infusionsoft->createContact([
            'Email'     => $uniqid . '@test.com',
            "_Products" => 'ipa,iea',
        ]);

        $user = User::create([
            'name'     => 'Test ' . $uniqid,
            'email'    => $uniqid . '@test.com',
            'password' => bcrypt($uniqid),
        ]);

        // attach IPA M1-3 & M5
        $user->completed_modules()->attach(Module::where('course_key', 'ipa')->limit(3)->get());
        $user->completed_modules()->attach(Module::where('name', 'IPA Module 5')->first());


        return $user;
    }*/
}
