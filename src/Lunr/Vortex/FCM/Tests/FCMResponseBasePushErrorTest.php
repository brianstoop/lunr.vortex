<?php

/**
 * This file contains the FCMResponseBasePushErrorTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2016 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Vortex\FCM\Tests;

use Lunr\Vortex\FCM\FCMResponse;
use Lunr\Vortex\PushNotificationStatus;
use ReflectionClass;

/**
 * This class contains tests for the constructor of the FCMResponse class
 * in case of a push notification error.
 *
 * @covers Lunr\Vortex\FCM\FCMResponse
 */
class FCMResponseBasePushErrorTest extends FCMResponseTest
{

    /**
     * Test constructor behavior for error of push notification in case of invalid JSON.
     *
     * @covers Lunr\Vortex\FCM\FCMResponse::__construct
     */
    public function testPushErrorInvalidJSON(): void
    {
        $http_code = 400;
        $content   = 'Field "collapse_key" must be a JSON string: 1463565451';
        $endpoint  = 'endpoint1';

        $this->response->status_code = $http_code;
        $this->response->body        = $content;

        $this->logger->expects($this->once())
                     ->method('warning')
                     ->with(
                         'Dispatching FCM notification failed for endpoint {endpoint}: {error}',
                         [ 'endpoint' => $endpoint, 'error' => "Invalid JSON ({$content})" ]
                     );

        $this->class = new FCMResponse($this->response, $this->logger, $endpoint, '{}');

        parent::baseSetUp($this->class);

        $this->assertPropertySame('logger', $this->logger);
        $this->assertPropertySame('endpoint', $endpoint);
        $this->assertPropertyEquals('status', PushNotificationStatus::ERROR);
        $this->assertPropertyEquals('content', $content);
        $this->assertPropertyEquals('http_code', $http_code);
    }

    /**
     * Test constructor behavior for error of push notification in case of authentication error.
     *
     * @covers Lunr\Vortex\FCM\FCMResponse::__construct
     */
    public function testPushErrorAuthenticationError(): void
    {
        $http_code = 401;
        $content   = 'stuff';
        $endpoint  = 'endpoint1';

        $this->response->status_code = $http_code;
        $this->response->body        = $content;

        $this->logger->expects($this->once())
                     ->method('warning')
                     ->with(
                         'Dispatching FCM notification failed for endpoint {endpoint}: {error}',
                         [ 'endpoint' => $endpoint, 'error' => 'Error with authentication' ]
                     );

        $this->class = new FCMResponse($this->response, $this->logger, $endpoint, '{}');

        parent::baseSetUp($this->class);

        $this->assertPropertySame('logger', $this->logger);
        $this->assertPropertySame('endpoint', $endpoint);
        $this->assertPropertyEquals('status', PushNotificationStatus::ERROR);
        $this->assertPropertyEquals('content', $content);
        $this->assertPropertyEquals('http_code', $http_code);
    }

    /**
     * Unit test data provider for internal error http codes.
     *
     * @return array $data http code
     */
    public function internalErrorHTTPCodeDataProvider()
    {
        $data = [];

        $data[] = [ 500 ];
        $data[] = [ 501 ];
        $data[] = [ 503 ];
        $data[] = [ 599 ];

        return $data;
    }

    /**
     * Test constructor behavior for error of push notification in case of internal error.
     *
     * @param int $http_code HTTP code received
     *
     * @dataProvider internalErrorHTTPCodeDataProvider
     * @covers       Lunr\Vortex\FCM\FCMResponse::__construct
     */
    public function testPushErrorInternalError($http_code): void
    {
        $content  = 'stuff';
        $endpoint = 'endpoint1';

        $this->response->status_code = $http_code;
        $this->response->body        = $content;

        $this->logger->expects($this->once())
                     ->method('warning')
                     ->with(
                         'Dispatching FCM notification failed for endpoint {endpoint}: {error}',
                         [ 'endpoint' => $endpoint, 'error' => 'Internal error' ]
                     );

        $this->class = new FCMResponse($this->response, $this->logger, $endpoint, '{}');

        parent::baseSetUp($this->class);

        $this->assertPropertySame('logger', $this->logger);
        $this->assertPropertySame('endpoint', $endpoint);
        $this->assertPropertyEquals('status', PushNotificationStatus::TEMPORARY_ERROR);
        $this->assertPropertyEquals('content', $content);
        $this->assertPropertyEquals('http_code', $http_code);
    }

    /**
     * Unit test data provider for unknown error http codes.
     *
     * @return array $data http code
     */
    public function unknownErrorHTTPCodeDataProvider()
    {
        $data = [];

        $data[] = [ 404 ];
        $data[] = [ 403 ];

        return $data;
    }

    /**
     * Test constructor behavior for error of push notification in case of unknown error.
     *
     * @param int $http_code HTTP code received
     *
     * @dataProvider unknownErrorHTTPCodeDataProvider
     * @covers       Lunr\Vortex\FCM\FCMResponse::__construct
     */
    public function testPushErrorUnknownError($http_code): void
    {
        $content  = 'stuff';
        $endpoint = 'endpoint1';

        $this->response->status_code = $http_code;
        $this->response->body        = $content;

        $this->logger->expects($this->once())
                     ->method('warning')
                     ->with(
                         'Dispatching FCM notification failed for endpoint {endpoint}: {error}',
                         [ 'endpoint' => $endpoint, 'error' => 'Unknown error' ]
                     );

        $this->class = new FCMResponse($this->response, $this->logger, $endpoint, '{}');

        parent::baseSetUp($this->class);

        $this->assertPropertySame('logger', $this->logger);
        $this->assertPropertySame('endpoint', $endpoint);
        $this->assertPropertyEquals('status', PushNotificationStatus::UNKNOWN);
        $this->assertPropertyEquals('content', $content);
        $this->assertPropertyEquals('http_code', $http_code);
    }

}

?>
