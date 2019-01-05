<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\InfusionsoftClientInterface;
use Prophecy\Prophecy\ObjectProphecy;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;

class ModuleReminderAssignerApiTest extends TestCase
{
    use WithFaker;

    private const FINAL_REMINDER_TAG_ID = 154;

    /**
     * @dataProvider dataProvider
     *
     * @param array  $customerData
     * @param bool   $isSuccess
     * @param int    $expectedTagId
     * @param string $expectedMessage
     * @param int    $expectedCode
     */
    public function testAssignReminderTag(
        array $customerData,
        bool $isSuccess,
        int $expectedTagId,
        string $expectedMessage,
        int $expectedCode
    ): void {
        $customerData['Email'] = $this->faker->email();
        $customerData['Id']    = $this->faker->numberBetween(1, 10000);

        /** @var InfusionsoftClientInterface|ObjectProphecy $infusionsoftClientProphecy */
        $infusionsoftClientProphecy = $this->prophesize(InfusionsoftClientInterface::class);

        $infusionsoftClientProphecy
            ->getContact($customerData['Email'])
            ->willReturn($customerData)
            ->shouldBeCalledTimes(1);

        $infusionsoftClientProphecy
            ->addTag($customerData['Id'], $expectedTagId)
            ->willReturn($isSuccess)
            ->shouldBeCalledTimes(1);

        $this->app->instance(InfusionsoftClientInterface::class, $infusionsoftClientProphecy->reveal());

        $response = $this->json(
            'POST',
            '/api/module_reminder_assigner',
            ['contact_email' => $customerData['Email']]
        );

        $response
            ->assertStatus($expectedCode)
            ->assertJson([
                'success' => $isSuccess,
                'message' => $expectedMessage,
            ]);
    }

    public function dataProvider(): array
    {
        return [
            [
                'customerData'    => [
                    '_Products' => 'ipa',
                ],
                'isSuccess'       => true,
                'expectedTagId'   => 110,
                'expectedMessage' => "Adding tag 'Start IPA Module 1 Reminders' succeeded",
                'expectedCode'    => 201,
            ],
            [
                'customerData'    => [
                    '_Products' => 'ipa',
                ],
                'isSuccess'       => false,
                'expectedTagId'   => 110,
                'expectedMessage' => "Adding tag 'Start IPA Module 1 Reminders' failed",
                'expectedCode'    => 422,
            ],
            [
                'customerData'    => [
                    '_Products' => 'ipa',
                    'Groups'    => '122',
                ],
                'isSuccess'       => true,
                'expectedTagId'   => self::FINAL_REMINDER_TAG_ID,
                'expectedMessage' => "Adding final tag 'Module reminders completed' succeeded",
                'expectedCode'    => 201,
            ],
            [
                'customerData'    => [
                    '_Products' => 'iea,ipa',
                    'Groups'    => 110,
                ],
                'isSuccess'       => true,
                'expectedTagId'   => 124,
                'expectedMessage' => "Adding tag 'Start IEA Module 1 Reminders' succeeded",
                'expectedCode'    => 201,
            ],
            [
                'customerData'    => [
                    '_Products' => 'iea,ipa',
                    'Groups'    => '110,134',
                ],
                'isSuccess'       => true,
                'expectedTagId'   => 136,
                'expectedMessage' => "Adding tag 'Start IEA Module 7 Reminders' succeeded",
                'expectedCode'    => 201,
            ],
            [
                'customerData'    => [
                    '_Products' => 'iea,ipa',
                    'Groups'    => '114,134,136',
                ],
                'isSuccess'       => true,
                'expectedTagId'   => 116,
                'expectedMessage' => "Adding tag 'Start IPA Module 4 Reminders' succeeded",
                'expectedCode'    => 201,
            ],
            [
                'customerData'    => [
                    '_Products' => 'iea,ipa',
                    'Groups'    => '114,134,136,122',
                ],
                'isSuccess'       => true,
                'expectedTagId'   => self::FINAL_REMINDER_TAG_ID,
                'expectedMessage' => "Adding final tag 'Module reminders completed' succeeded",
                'expectedCode'    => 201,
            ],
            [
                'customerData'    => [
                    '_Products' => 'iea,ipa',
                    'Groups'    => '114,134,136,122',
                ],
                'isSuccess'       => false,
                'expectedTagId'   => self::FINAL_REMINDER_TAG_ID,
                'expectedMessage' => "Adding final tag 'Module reminders completed' failed",
                'expectedCode'    => 422,
            ],
        ];
    }
}
