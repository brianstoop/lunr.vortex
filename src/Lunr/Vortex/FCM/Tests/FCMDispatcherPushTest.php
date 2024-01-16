<?php

/**
 * This file contains the FCMDispatcherPushTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2017 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Vortex\FCM\Tests;

use Lunr\Vortex\PushNotificationStatus;
use Lunr\Vortex\APNS\APNSPayload;
use Lunr\Vortex\Email\EmailPayload;
use Lunr\Vortex\JPush\JPushMessagePayload;
use Lunr\Vortex\WNS\WNSTilePayload;
use WpOrg\Requests\Exception as RequestsException;

/**
 * This class contains test for the push() method of the FCMDispatcher class.
 *
 * @covers Lunr\Vortex\FCM\FCMDispatcher
 */
class FCMDispatcherPushTest extends FCMDispatcherTest
{

    /**
     * Unit test data provider for unsupported payload objects.
     *
     * @return array Unsupported payload objects
     */
    public static function unsupportedPayloadProvider(): array
    {
        $data          = [];
        $data['apns']  = [ new APNSPayload() ];
        $data['email'] = [ new EmailPayload() ];
        $data['jpush'] = [ new JPushMessagePayload() ];
        $data['wns']   = [ new WNSTilePayload() ];

        return $data;
    }

    /**
     * Test that push() throws an exception is the passed payload object is not supported.
     *
     * @param object $payload Unsupported payload object
     *
     * @dataProvider unsupportedPayloadProvider
     * @covers       Lunr\Vortex\FCM\FCMDispatcher::push
     */
    public function testPushingWithUnsupportedPayloadThrowsException($payload): void
    {
        $endpoints = [ 'endpoint' ];

        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Invalid payload object!');

        $this->class->push($payload, $endpoints);
    }

    /**
     * Test that push() throws exception when no endpoints are provided.
     *
     * @covers Lunr\Vortex\FCM\FCMDispatcher::push
     */
    public function testPushWithEmptyEndpointsThrowException(): void
    {
        $endpoints = [];

        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('No endpoints provided!');

        $this->class->push($this->payload, $endpoints);
    }

    /**
     * Test that push() returns response when oauth_token is null.
     *
     * @covers Lunr\Vortex\FCM\FCMDispatcher::push
     */
    public function testPushWhenOAuthTokenIsNull(): void
    {
        $endpoints = [ 'endpoint' ];

        $this->payload->expects($this->once())
                      ->method('get_payload')
                      ->willReturn('{"collapse_key":"abcde-12345"}');

        $this->set_reflection_property_value('oauth_token', NULL);

        $this->logger->expects($this->exactly(2))
                     ->method('warning')
                     ->withConsecutive(
                        [
                            'Tried to push FCM notification to {endpoint} but wasn\'t authenticated.',
                            [ 'endpoint' => 'endpoint' ]
                        ],
                        [
                            'Dispatching FCM notification failed for endpoint {endpoint}: {error}',
                            [ 'endpoint' => 'endpoint', 'error' => 'Error with authentication' ]
                        ]
                    );

        $result = $this->class->push($this->payload, $endpoints);

        $this->assertInstanceOf('Lunr\Vortex\FCM\FCMResponse', $result);
    }

    /**
     * Test that push() returns response when project_id is null.
     *
     * @covers Lunr\Vortex\FCM\FCMDispatcher::push
     */
    public function testPushWhenProjectIdIsNull(): void
    {
        $endpoints = [ 'endpoint' ];

        $this->payload->expects($this->once())
                      ->method('get_payload')
                      ->willReturn('{"collapse_key":"abcde-12345"}');

        $this->set_reflection_property_value('oauth_token', 'test');
        $this->set_reflection_property_value('project_id', NULL);

        $this->logger->expects($this->exactly(2))
                     ->method('warning')
                     ->withConsecutive(
                        [
                            'Tried to push FCM notification to {endpoint} but project id is not provided.',
                            [ 'endpoint' => 'endpoint' ]
                        ],
                        [
                            'Dispatching FCM notification failed for endpoint {endpoint}: {error}',
                            [ 'endpoint' => 'endpoint', 'error' => 'Invalid JSON ()' ]
                        ]
                    );

        $result = $this->class->push($this->payload, $endpoints);

        $this->assertInstanceOf('Lunr\Vortex\FCM\FCMResponse', $result);
    }

    /**
     * Test that push() resets the properties after a push.
     *
     * @covers Lunr\Vortex\FCM\FCMDispatcher::push
     */
    public function testPushResetsProperties(): void
    {
        $endpoints = [ 'endpoint' ];

        $this->payload->expects($this->once())
                      ->method('get_payload')
                      ->willReturn('{"collapse_key":"abcde-12345"}');

        $this->set_reflection_property_value('oauth_token', 'oauth_token');
        $this->set_reflection_property_value('project_id', 'fcm-project');

        $response = $this->getMockBuilder('WpOrg\Requests\Response')->getMock();

        $this->http->expects($this->once())
                   ->method('post')
                   ->will($this->returnValue($response));

        $this->class->push($this->payload, $endpoints);

        $this->assertPropertyEquals('oauth_token', 'oauth_token');
        $this->assertPropertyEquals('project_id', 'fcm-project');
    }

    /**
     * Test that push() works as expected when the request failed.
     *
     * @covers Lunr\Vortex\FCM\FCMDispatcher::push
     */
    public function testPushWithFailedRequest(): void
    {
        $this->set_reflection_property_value('oauth_token', 'oauth_token');
        $this->set_reflection_property_value('project_id', 'fcm-project');

        $this->mock_function('curl_errno', function () { return 10; });

        $endpoints = [ 'endpoint' ];

        $headers = [
            'Content-Type'  => 'application/json',
            'Authorization' => 'Bearer oauth_token',
        ];

        $url  = 'https://fcm.googleapis.com/v1/projects/fcm-project/messages:send';
        $post = '{"to":"endpoint"}';

        $options = [
            'timeout'         => 15,
            'connect_timeout' => 15
        ];

        $this->http->expects($this->once())
                   ->method('post')
                   ->with($this->equalTo($url), $this->equalTo($headers), $this->equalTo($post), $this->equalTo($options))
                   ->will($this->throwException(new RequestsException('cURL error 10: Request error', 'curlerror', NULL)));

        $message = 'Dispatching FCM notification(s) failed: {message}';
        $context = [ 'message' => 'cURL error 10: Request error' ];

        $this->logger->expects($this->exactly(2))
                     ->method('warning')
                     ->withConsecutive([ $message, $context ], [ 'Dispatching FCM notification failed for endpoint {endpoint}: {error}' ]);

        $result = $this->class->push($this->payload, $endpoints);

        $this->assertInstanceOf('Lunr\Vortex\FCM\FCMResponse', $result);

        $this->unmock_function('curl_errno');
    }

    /**
     * Test that push() works as expected when the request failed.
     *
     * @covers Lunr\Vortex\FCM\FCMDispatcher::push
     */
    public function testPushWithTimeoutRequest()
    {
        $this->set_reflection_property_value('oauth_token', 'oauth_token');
        $this->set_reflection_property_value('project_id', 'fcm-project');

        $this->mock_function('curl_errno', function () { return 28; });

        $endpoints = [ 'endpoint' ];

        $headers = [
            'Content-Type'  => 'application/json',
            'Authorization' => 'Bearer oauth_token',
        ];

        $url  = 'https://fcm.googleapis.com/v1/projects/fcm-project/messages:send';
        $post = '{"to":"endpoint"}';

        $options = [
            'timeout'         => 15,
            'connect_timeout' => 15
        ];

        $this->http->expects($this->once())
                   ->method('post')
                   ->with($this->equalTo($url), $this->equalTo($headers), $this->equalTo($post), $this->equalTo($options))
                   ->will($this->throwException(new RequestsException('cURL error 28: Request timed out', 'curlerror', NULL)));

        $message = 'Dispatching FCM notification(s) failed: {message}';
        $context = [ 'message' => 'cURL error 28: Request timed out' ];

        $this->logger->expects($this->exactly(2))
                     ->method('warning')
                     ->withConsecutive([ $message, $context ], [ 'Dispatching FCM notification failed for endpoint {endpoint}: {error}' ]);

        $result = $this->class->push($this->payload, $endpoints);

        $this->assertInstanceOf('Lunr\Vortex\FCM\FCMResponse', $result);

        $this->assertSame($result->get_status('endpoint'), PushNotificationStatus::TEMPORARY_ERROR);

        $this->unmock_function('curl_errno');
    }

    /**
     * Test that push() sends correct request with no properties set except the endpoint.
     *
     * @covers Lunr\Vortex\FCM\FCMDispatcher::push
     */
    public function testPushRequestWithDefaultValues(): void
    {
        $this->set_reflection_property_value('oauth_token', 'oauth_token');
        $this->set_reflection_property_value('project_id', 'fcm-project');

        $endpoints = [ 'endpoint' ];

        $response = $this->getMockBuilder('WpOrg\Requests\Response')->getMock();

        $headers = [
            'Content-Type'  => 'application/json',
            'Authorization' => 'Bearer oauth_token',
        ];

        $url  = 'https://fcm.googleapis.com/v1/projects/fcm-project/messages:send';
        $post = '{"to":"endpoint"}';

        $options = [
            'timeout'         => 15,
            'connect_timeout' => 15
        ];

        $this->http->expects($this->once())
                   ->method('post')
                   ->with($this->equalTo($url), $this->equalTo($headers), $this->equalTo($post), $this->equalTo($options))
                   ->will($this->returnValue($response));

        $this->class->push($this->payload, $endpoints);
    }

    /**
     * Test that push() sends correct request with single endpoint.
     *
     * @covers Lunr\Vortex\FCM\FCMDispatcher::push
     */
    public function testPushRequestWithSingleEndpoint(): void
    {
        $this->set_reflection_property_value('oauth_token', 'oauth_token');
        $this->set_reflection_property_value('project_id', 'fcm-project');

        $endpoints = [ 'endpoint' ];

        $this->payload->expects($this->once())
                      ->method('get_payload')
                      ->willReturn('{"collapse_key":"abcde-12345"}');

        $response = $this->getMockBuilder('WpOrg\Requests\Response')->getMock();

        $headers = [
            'Content-Type'  => 'application/json',
            'Authorization' => 'Bearer oauth_token',
        ];

        $url  = 'https://fcm.googleapis.com/v1/projects/fcm-project/messages:send';
        $post = '{"collapse_key":"abcde-12345","to":"endpoint"}';

        $options = [
            'timeout'         => 15,
            'connect_timeout' => 15
        ];

        $this->http->expects($this->once())
                   ->method('post')
                   ->with($this->equalTo($url), $this->equalTo($headers), $this->equalTo($post), $this->equalTo($options))
                   ->will($this->returnValue($response));

        $this->class->push($this->payload, $endpoints);
    }

    /**
     * Test that push() generates a payload that doesn't encode unicode characters.
     *
     * @covers Lunr\Vortex\FCM\FCMDispatcher::push
     */
    public function testPushRequestWithMultibyteCharacters(): void
    {
        $this->set_reflection_property_value('oauth_token', 'oauth_token');
        $this->set_reflection_property_value('project_id', 'fcm-project');

        $endpoints = [ 'endpoint' ];

        $this->payload->expects($this->once())
                      ->method('get_payload')
                      ->willReturn('{"collapse_key":"abcde-12345","data":{"message":"凄い"}}');

        $response = $this->getMockBuilder('WpOrg\Requests\Response')->getMock();

        $headers = [
            'Content-Type'  => 'application/json',
            'Authorization' => 'Bearer oauth_token',
        ];

        $url  = 'https://fcm.googleapis.com/v1/projects/fcm-project/messages:send';
        $post = '{"collapse_key":"abcde-12345","data":{"message":"凄い"},"to":"endpoint"}';

        $options = [
            'timeout'         => 15,
            'connect_timeout' => 15
        ];

        $this->http->expects($this->once())
                   ->method('post')
                   ->with($url, $headers, $post, $options)
                   ->willReturn($response);

        $this->class->push($this->payload, $endpoints);
    }

}

?>
