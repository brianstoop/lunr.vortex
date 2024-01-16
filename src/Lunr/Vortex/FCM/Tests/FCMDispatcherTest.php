<?php

/**
 * This file contains the FCMDispatcherTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2017 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Vortex\FCM\Tests;

use Lcobucci\JWT\Builder;
use Lcobucci\JWT\UnencryptedToken;
use Lunr\Halo\LunrBaseTest;
use Lunr\Vortex\FCM\FCMDispatcher;
use Lunr\Vortex\FCM\FCMPayload;
use Psr\Log\LoggerInterface;
use WpOrg\Requests\Response;
use WpOrg\Requests\Session;

/**
 * This class contains common setup routines, providers
 * and shared attributes for testing the FCMDispatcher class.
 *
 * @covers Lunr\Vortex\FCM\FCMDispatcher
 */
abstract class FCMDispatcherTest extends LunrBaseTest
{
    /**
     * Mock instance of the Requests\Session class.
     * @var Session
     */
    protected $http;

    /**
     * Mock instance of the Requests\Response class.
     * @var Response
     */
    protected $response;

    /**
     * Mock instance of a Logger class.
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Mock instance of the FCM Payload class.
     * @var FCMPayload
     */
    protected $payload;

    /**
     * Mock instance of the token builder class.
     * @var Builder
     */
    protected $token_builder;

    /**
     * Mock instance of the token UnencryptedToken class.
     * @var UnencryptedToken
     */
    protected $token_plain;

    /**
     * Instance of the tested class.
     * @var FCMDispatcher
     */
    protected FCMDispatcher $class;

    /**
     * Testcase Constructor.
     */
    public function setUp(): void
    {
        $this->http     = $this->getMockBuilder('WpOrg\Requests\Session')->getMock();
        $this->response = $this->getMockBuilder('WpOrg\Requests\Response')->getMock();
        $this->logger   = $this->getMockBuilder('Psr\Log\LoggerInterface')->getMock();
        $this->payload  = $this->getMockBuilder('Lunr\Vortex\FCM\FCMPayload')
                              ->disableOriginalConstructor()
                              ->getMock();

        $this->token_builder = $this->getMockBuilder(Builder::class)->getMock();
        $this->token_plain   = $this->getMockBuilder(UnencryptedToken::class)->getMock();

        $this->class = new FCMDispatcher($this->http, $this->logger);

        parent::baseSetUp($this->class);
    }

    /**
     * Testcase Destructor.
     */
    public function tearDown(): void
    {
        unset($this->logger);
        unset($this->payload);
        unset($this->class);

        parent::tearDown();
    }

}

?>
