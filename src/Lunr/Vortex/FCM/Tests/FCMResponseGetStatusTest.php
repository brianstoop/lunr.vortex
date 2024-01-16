<?php

/**
 * This file contains the FCMResponseGetStatusTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2013 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Vortex\FCM\Tests;

use Lunr\Vortex\FCM\FCMResponse;
use Lunr\Vortex\PushNotificationStatus;
use ReflectionClass;

/**
 * This class contains tests for the get_status function of the FCMResponse class.
 *
 * @covers Lunr\Vortex\FCM\FCMResponse
 */
class FCMResponseGetStatusTest extends FCMResponseTest
{

    /**
     * Testcase constructor.
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $content = file_get_contents(TEST_STATICS . '/Vortex/fcm/response_single_success.json');

        $this->response->status_code = 200;
        $this->response->body        = $content;

        $this->class = new FCMResponse($this->response, $this->logger, 'endpoint1', '{}');

        parent::baseSetUp($this->class);
    }

    /**
     * Test the get_status() returns unknown status if wrong endpoint is provided.
     *
     * @covers Lunr\Vortex\FCM\FCMResponse::get_status
     */
    public function testGetStatusReturnsUnknown(): void
    {
        $this->set_reflection_property_value('status', PushNotificationStatus::SUCCESS);

        $result = $this->class->get_status('endpoint_param');

        $this->assertSame(PushNotificationStatus::UNKNOWN, $result);
    }

    /**
     * Test the get_status() succeeds.
     *
     * @covers Lunr\Vortex\FCM\FCMResponse::get_status
     */
    public function testGetStatusSucceeds(): void
    {
        $this->set_reflection_property_value('status', PushNotificationStatus::SUCCESS);

        $result = $this->class->get_status('endpoint1');

        $this->assertSame(PushNotificationStatus::SUCCESS, $result);
    }

}

?>
