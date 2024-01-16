<?php

/**
 * This file contains the FCMDispatcherSetTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2013 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Vortex\FCM\Tests;

use DateTimeImmutable;
use Lcobucci\JWT\Token\Builder;
use WpOrg\Requests\Exception as RequestsException;
use WpOrg\Requests\Response;

/**
 * This class contains tests for the setters of the FCMDispatcher class.
 *
 * @covers Lunr\Vortex\FCM\FCMDispatcher
 */
class FCMDispatcherSetTest extends FCMDispatcherTest
{

    /**
     * Test that set_project_id() sets the endpoint.
     *
     * @covers Lunr\Vortex\FCM\FCMDispatcher::set_project_id
     */
    public function testSetProjectIDSetsProjectId(): void
    {
        $this->class->set_project_id('project_id');

        $this->assertPropertyEquals('project_id', 'project_id');
    }

    /**
     * Test the return of set_project_id().
     *
     * @covers Lunr\Vortex\FCM\FCMDispatcher::set_project_id
     */
    public function testSetProjectIdReturnsSelfReference(): void
    {
        $this->assertEquals($this->class, $this->class->set_project_id('project_id'));
    }

    /**
     * Test set_oauth_token when fetching token fails.
     *
     * @covers Lunr\Vortex\FCM\FCMDispatcher::set_oauth_token
     */
    public function testSetOAuthTokenWhenFetchingTokenFails(): void
    {
        if (!extension_loaded('uopz'))
        {
            $this->markTestSkipped('The uopz extension is not available.');
        }

        $dti = new DateTimeImmutable();

        uopz_set_mock(DateTimeImmutable::class, $dti);
        uopz_set_mock(Builder::class, $this->token_builder);

        $this->token_builder->expects($this->once())
                            ->method('issuedBy')
                            ->with('iss')
                            ->willReturnSelf();

        $this->token_builder->expects($this->once())
                            ->method('permittedFor')
                            ->with('https://oauth2.googleapis.com/token')
                            ->willReturnSelf();

        $this->token_builder->expects($this->once())
                            ->method('issuedAt')
                            ->with($dti)
                            ->willReturnSelf();

        $this->token_builder->expects($this->once())
                            ->method('expiresAt')
                            ->with($dti->modify('+1 hour'))
                            ->willReturnSelf();

        $this->token_builder->expects($this->once())
                            ->method('withClaim')
                            ->with('scope', 'https://www.googleapis.com/auth/firebase.messaging')
                            ->willReturnSelf();

        $this->token_builder->expects($this->exactly(2))
                            ->method('withHeader')
                            ->withConsecutive(
                                [ 'alg', 'RS2256' ],
                                [ 'typ', 'JWT' ],
                            )
                            ->willReturnSelf();

        uopz_set_return($this->token_builder::class, 'getToken', $this->token_plain);

        $this->token_plain->expects($this->once())
                          ->method('toString')
                          ->willReturn('jwt_token');

        $headers = [
            'Content-Type'  => 'application/json'
        ];

        $payload = [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion'  => 'jwt_token',
        ];

        $options = [
            'timeout'         => 15,
            'connect_timeout' => 15
        ];

        $this->http->expects($this->once())
                   ->method('post')
                   ->with('https://oauth2.googleapis.com/token', $headers, json_encode($payload, JSON_UNESCAPED_UNICODE), $options)
                   ->willThrowException(new RequestsException('cURL error 10: Request error', 'curlerror', NULL));

        $this->logger->expects($this->once())
                     ->method('warning')
                     ->with('Fetching OAuth token for FCM notification(s) failed: {message}', [ 'message' => 'cURL error 10: Request error' ]);

        $this->assertEquals($this->class, $this->class->set_oauth_token('iss', 'private_key'));

        uopz_unset_return($this->token_builder::class, 'getToken');
        uopz_unset_mock(DateTimeImmutable::class);
        uopz_unset_mock(Builder::class);
    }

    /**
     * Test set_oauth_token when processing json response fails.
     *
     * @covers Lunr\Vortex\FCM\FCMDispatcher::set_oauth_token
     */
    public function testSetOAuthTokenWhenProcessingJsonResponseFails(): void
    {
        if (!extension_loaded('uopz'))
        {
            $this->markTestSkipped('The uopz extension is not available.');
        }

        $dti = new DateTimeImmutable();

        uopz_set_mock(DateTimeImmutable::class, $dti);
        uopz_set_mock(Builder::class, $this->token_builder);

        $this->token_builder->expects($this->once())
                            ->method('issuedBy')
                            ->with('iss')
                            ->willReturnSelf();

        $this->token_builder->expects($this->once())
                            ->method('permittedFor')
                            ->with('https://oauth2.googleapis.com/token')
                            ->willReturnSelf();

        $this->token_builder->expects($this->once())
                            ->method('issuedAt')
                            ->with($dti)
                            ->willReturnSelf();

        $this->token_builder->expects($this->once())
                            ->method('expiresAt')
                            ->with($dti->modify('+1 hour'))
                            ->willReturnSelf();

        $this->token_builder->expects($this->once())
                            ->method('withClaim')
                            ->with('scope', 'https://www.googleapis.com/auth/firebase.messaging')
                            ->willReturnSelf();

        $this->token_builder->expects($this->exactly(2))
                            ->method('withHeader')
                            ->withConsecutive(
                                [ 'alg', 'RS2256' ],
                                [ 'typ', 'JWT' ],
                            )
                            ->willReturnSelf();

        uopz_set_return($this->token_builder::class, 'getToken', $this->token_plain);

        $this->token_plain->expects($this->once())
                          ->method('toString')
                          ->willReturn('jwt_token');

        $headers = [
            'Content-Type'  => 'application/json'
        ];

        $payload = [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion'  => 'jwt_token',
        ];

        $options = [
            'timeout'         => 15,
            'connect_timeout' => 15
        ];

        $response = new Response();

        $response->body = '{';

        $this->http->expects($this->once())
                   ->method('post')
                   ->with('https://oauth2.googleapis.com/token', $headers, json_encode($payload, JSON_UNESCAPED_UNICODE), $options)
                   ->willReturn($response);

        $this->logger->expects($this->once())
                     ->method('warning')
                     ->with(
                         'Processing json response for fetching OAuth token for FCM notification(s) failed: {message}',
                         [ 'message' => 'Syntax error' ]
                     );

        $this->assertEquals($this->class, $this->class->set_oauth_token('iss', 'private_key'));

        uopz_unset_return($this->token_builder::class, 'getToken');
        uopz_unset_mock(DateTimeImmutable::class);
        uopz_unset_mock(Builder::class);
    }

    /**
     * Test set_oauth_token when processing response fails when no access token is provided.
     *
     * @covers Lunr\Vortex\FCM\FCMDispatcher::set_oauth_token
     */
    public function testSetOAuthTokenFailsWhenNotAccessTokenIsProvided(): void
    {
        if (!extension_loaded('uopz'))
        {
            $this->markTestSkipped('The uopz extension is not available.');
        }

        $dti = new DateTimeImmutable();

        uopz_set_mock(DateTimeImmutable::class, $dti);
        uopz_set_mock(Builder::class, $this->token_builder);

        $this->token_builder->expects($this->once())
                            ->method('issuedBy')
                            ->with('iss')
                            ->willReturnSelf();

        $this->token_builder->expects($this->once())
                            ->method('permittedFor')
                            ->with('https://oauth2.googleapis.com/token')
                            ->willReturnSelf();

        $this->token_builder->expects($this->once())
                            ->method('issuedAt')
                            ->with($dti)
                            ->willReturnSelf();

        $this->token_builder->expects($this->once())
                            ->method('expiresAt')
                            ->with($dti->modify('+1 hour'))
                            ->willReturnSelf();

        $this->token_builder->expects($this->once())
                            ->method('withClaim')
                            ->with('scope', 'https://www.googleapis.com/auth/firebase.messaging')
                            ->willReturnSelf();

        $this->token_builder->expects($this->exactly(2))
                            ->method('withHeader')
                            ->withConsecutive(
                                [ 'alg', 'RS2256' ],
                                [ 'typ', 'JWT' ],
                            )
                            ->willReturnSelf();

        uopz_set_return($this->token_builder::class, 'getToken', $this->token_plain);

        $this->token_plain->expects($this->once())
                          ->method('toString')
                          ->willReturn('jwt_token');

        $headers = [
            'Content-Type'  => 'application/json'
        ];

        $payload = [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion'  => 'jwt_token',
        ];

        $options = [
            'timeout'         => 15,
            'connect_timeout' => 15
        ];

        $response = new Response();

        $response->body = '{"token":"oauth_token1"}';

        $this->http->expects($this->once())
                   ->method('post')
                   ->with('https://oauth2.googleapis.com/token', $headers, json_encode($payload, JSON_UNESCAPED_UNICODE), $options)
                   ->willReturn($response);

        $this->logger->expects($this->once())
                     ->method('warning')
                     ->with(
                         'Processing response for fetching OAuth token for FCM notification(s) failed: No access token in the response body'
                     );

        $this->assertEquals($this->class, $this->class->set_oauth_token('iss', 'private_key'));

        uopz_unset_return($this->token_builder::class, 'getToken');
        uopz_unset_mock(DateTimeImmutable::class);
        uopz_unset_mock(Builder::class);
    }

    /**
     * Test set_oauth_token when fetching token succeeds.
     *
     * @covers Lunr\Vortex\FCM\FCMDispatcher::set_oauth_token
     */
    public function testSetOAuthTokenWhenFetchingTokenSucceeds(): void
    {
        if (!extension_loaded('uopz'))
        {
            $this->markTestSkipped('The uopz extension is not available.');
        }

        $dti = new DateTimeImmutable();

        uopz_set_mock(DateTimeImmutable::class, $dti);
        uopz_set_mock(Builder::class, $this->token_builder);

        $this->token_builder->expects($this->once())
                            ->method('issuedBy')
                            ->with('iss')
                            ->willReturnSelf();

        $this->token_builder->expects($this->once())
                            ->method('permittedFor')
                            ->with('https://oauth2.googleapis.com/token')
                            ->willReturnSelf();

        $this->token_builder->expects($this->once())
                            ->method('issuedAt')
                            ->with($dti)
                            ->willReturnSelf();

        $this->token_builder->expects($this->once())
                            ->method('expiresAt')
                            ->with($dti->modify('+1 hour'))
                            ->willReturnSelf();

        $this->token_builder->expects($this->once())
                            ->method('withClaim')
                            ->with('scope', 'https://www.googleapis.com/auth/firebase.messaging')
                            ->willReturnSelf();

        $this->token_builder->expects($this->exactly(2))
                            ->method('withHeader')
                            ->withConsecutive(
                                [ 'alg', 'RS2256' ],
                                [ 'typ', 'JWT' ],
                            )
                            ->willReturnSelf();

        uopz_set_return($this->token_builder::class, 'getToken', $this->token_plain);

        $this->token_plain->expects($this->once())
                          ->method('toString')
                          ->willReturn('jwt_token');

        $headers = [
            'Content-Type'  => 'application/json'
        ];

        $payload = [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion'  => 'jwt_token',
        ];

        $options = [
            'timeout'         => 15,
            'connect_timeout' => 15
        ];

        $response = new Response();

        $response->body = '{"access_token":"oauth_token1"}';

        $this->http->expects($this->once())
                   ->method('post')
                   ->with('https://oauth2.googleapis.com/token', $headers, json_encode($payload, JSON_UNESCAPED_UNICODE), $options)
                   ->willReturn($response);

        $this->assertEquals($this->class, $this->class->set_oauth_token('iss', 'private_key'));
        $this->assertPropertySame('oauth_token', 'oauth_token1');

        uopz_unset_return($this->token_builder::class, 'getToken');
        uopz_unset_mock(DateTimeImmutable::class);
        uopz_unset_mock(Builder::class);
    }

}

?>
