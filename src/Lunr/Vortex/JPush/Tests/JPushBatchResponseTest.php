<?php

/**
 * This file contains the JPushBatchResponseTest class.
 *
 * @package    Lunr\Vortex\JPush
 * @author     Sean Molenaar <s.molenaar@m2mobi.com>
 * @copyright  2020, M2Mobi BV, Amsterdam, The Netherlands
 * @license    http://lunr.nl/LICENSE MIT License
 */

namespace Lunr\Vortex\JPush\Tests;

use Lunr\Halo\LunrBaseTest;
use PHPUnit\Framework\MockObject\MockObject;
use WpOrg\Requests\Response;
use WpOrg\Requests\Session;

/**
 * This class contains common setup routines, providers
 * and shared attributes for testing the JPushBatchResponse class.
 *
 * @covers \Lunr\Vortex\JPush\JPushBatchResponse
 */
abstract class JPushBatchResponseTest extends LunrBaseTest
{

    /**
     * Mock instance of the Requests\Session class.
     * @var Session|MockObject
     */
    protected $http;

    /**
     * Mock instance of the Logger class.
     * @var LoggerInterface|MockObject
     */
    protected $logger;

    /**
     * Mock instance of the Requests\Response class.
     * @var Response|MockObject
     */
    protected $response;

    /**
     * Testcase Constructor.
     *
     * @return void
     */
    public function setUp(): void
    {
        $this->http = $this->getMockBuilder('WpOrg\Requests\Session')->getMock();

        $this->logger = $this->getMockBuilder('Psr\Log\LoggerInterface')->getMock();

        $this->response = $this->getMockBuilder('WpOrg\Requests\Response')->getMock();
    }

    /**
     * Testcase Destructor.
     */
    public function tearDown(): void
    {
        unset($this->http);
        unset($this->logger);
        unset($this->response);
        unset($this->class);
        unset($this->reflection);
    }

}

?>
