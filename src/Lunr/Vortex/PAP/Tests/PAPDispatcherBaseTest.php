<?php

/**
 * This file contains the PAPDispatcherBaseTest class.
 *
 * @package    Lunr\Vortex\PAP
 * @author     Leonidas Diamantis <leonidas@m2mobi.com>
 * @copyright  2014-2018, M2Mobi BV, Amsterdam, The Netherlands
 * @license    http://lunr.nl/LICENSE MIT License
 */

namespace Lunr\Vortex\PAP\Tests;

use Lunr\Halo\PropertyTraits\PsrLoggerTestTrait;

/**
 * This class contains test for the constructor of the PAPDispatcher class.
 *
 * @covers Lunr\Vortex\PAP\PAPDispatcher
 */
class PAPDispatcherBaseTest extends PAPDispatcherTest
{

    use PsrLoggerTestTrait;

    /**
     * Test that the auth_token is set to an empty string by default.
     */
    public function testAuthTokenIsEmptyString(): void
    {
        $this->assertPropertyEmpty('auth_token');
    }

    /**
     * Test that the password is set to an empty string by default.
     */
    public function testPasswordIsEmptyString(): void
    {
        $this->assertPropertyEmpty('password');
    }

    /**
     * Test that the content provider id is set to an empty string by default.
     */
    public function testCidIsEmptyString(): void
    {
        $this->assertPropertyEmpty('cid');
    }

    /**
     * Test that the push id is set to an empty string by default.
     */
    public function testPushIdIsEmptyString(): void
    {
        $this->assertPropertyEmpty('push_id');
    }

    /**
     * Test that the passed Requests_Session object is set correctly.
     */
    public function testRequestsSessionIsSetCorrectly(): void
    {
        $this->assertSame($this->http, $this->get_reflection_property_value('http'));
    }

    /**
     * Test that construct_pap_control_xml() creates an XML with empty fields when the push_id & private properties are empty.
     *
     * @covers Lunr\Vortex\PAP\PAPDispatcher::construct_pap_control_xml
     */
    public function testConstructControlXMLWithEmptyInputConstructsEmptyXML(): void
    {
        $method = $this->reflection->getMethod('construct_pap_control_xml');
        $method->setAccessible(TRUE);

        $control_xml = $method->invokeArgs($this->class, [ $this->payload, 'endpoint' ]);

        $xml_file = file_get_contents(TEST_STATICS . '/Vortex/pap/request_control_empty.xml');

        $this->assertEquals($control_xml, $xml_file);
    }

    /**
     * Test that construct_pap_control_xml() creates a proper XML when the push_id & private properties are present.
     *
     * @covers Lunr\Vortex\PAP\PAPDispatcher::construct_pap_control_xml
     */
    public function testConstructControlXMLConstructsXMLCorrectly(): void
    {
        $this->set_reflection_property_value('auth_token', 'auth_token');
        $this->set_reflection_property_value('push_id', 'endpoint12345');

        $this->payload->expects($this->once())
                      ->method('get_priority')
                      ->willReturn('deliverbefore');

        $method = $this->reflection->getMethod('construct_pap_control_xml');
        $method->setAccessible(TRUE);

        $control_xml = $method->invokeArgs($this->class, [ $this->payload, 'endpoint' ]);

        $xml_file = file_get_contents(TEST_STATICS . '/Vortex/pap/request_control.xml');

        $this->assertEquals($control_xml, $xml_file);
    }

    /**
     * Test that construct_pap_data() creates a proper XML when the push_id & private properties are present.
     *
     * @covers Lunr\Vortex\PAP\PAPDispatcher::construct_pap_data
     */
    public function testConstructPAPDataConstructsHeadersCorrectly(): void
    {
        $this->mock_function('microtime', function () { return 12345; });

        $this->payload->expects($this->once())
                      ->method('get_priority')
                      ->willReturn('deliverbefore');

        $this->set_reflection_property_value('auth_token', 'auth_token');

        $this->payload->expects($this->once())
                      ->method('get_payload')
                      ->willReturn('{"message":"test"}');

        $method = $this->reflection->getMethod('construct_pap_data');
        $method->setAccessible(TRUE);

        $request_headers = $method->invokeArgs($this->class, [ $this->payload, 'endpoint' ]);

        $request_file = file_get_contents(TEST_STATICS . '/Vortex/pap/request_custom_headers.txt');

        $this->assertEquals($request_headers, $request_file);

        $this->unmock_function('microtime');
    }

    /**
     * Test get_new_response_object_for_failed_request().
     *
     * @covers Lunr\Vortex\PAP\PAPDispatcher::get_new_response_object_for_failed_request
     */
    public function testGetNewResponseObjectForFailedRequest(): void
    {
        $this->set_reflection_property_value('cid', 'papcid');

        $method = $this->get_accessible_reflection_method('get_new_response_object_for_failed_request');

        $result = $method->invoke($this->class);

        $this->assertInstanceOf('\Requests_Response', $result);
        $this->assertEquals('https://cppapcid.pushapi.na.blackberry.com/mss/PD_pushRequest', $result->url);
    }

}

?>
