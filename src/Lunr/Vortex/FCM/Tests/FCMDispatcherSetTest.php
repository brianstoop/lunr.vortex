<?php

/**
 * This file contains the FCMDispatcherSetTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2013 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Vortex\FCM\Tests;

use RuntimeException;

/**
 * This class contains tests for the setters of the FCMDispatcher class.
 *
 * @covers Lunr\Vortex\FCM\FCMDispatcher
 */
class FCMDispatcherSetTest extends FCMDispatcherTest
{

    /**
     * Test that set_project_id() sets the project_id.
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
     * Test that set_client_email() sets the client_email.
     *
     * @covers Lunr\Vortex\FCM\FCMDispatcher::set_client_email
     */
    public function testSetClientEmailSetsClientEmail(): void
    {
        $this->class->set_client_email('email');

        $this->assertPropertyEquals('client_email', 'email');
    }

    /**
     * Test the return of set_client_email().
     *
     * @covers Lunr\Vortex\FCM\FCMDispatcher::set_client_email
     */
    public function testSetClientEmailReturnsSelfReference(): void
    {
        $this->assertEquals($this->class, $this->class->set_client_email('email'));
    }

    /**
     * Test that set_private_key() sets the private_key.
     *
     * @covers Lunr\Vortex\FCM\FCMDispatcher::set_private_key
     */
    public function testSetPrivateKeySetsPrivateKey(): void
    {
        $this->class->set_private_key('key');

        $this->assertPropertyEquals('private_key', 'key');
    }

    /**
     * Test the return of set_private_key().
     *
     * @covers Lunr\Vortex\FCM\FCMDispatcher::set_private_key
     */
    public function testSetPrivateKeyReturnsSelfReference(): void
    {
        $this->assertEquals($this->class, $this->class->set_private_key('key'));
    }

    /**
     * Test that set_private_key_by_file() returns exception when the file does not exists.
     *
     * @covers Lunr\Vortex\FCM\FCMDispatcher::set_private_key_by_file
     */
    public function testSetPrivateKeyByFileReturnExceptionIfNotIsReadable(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('File does not exists or is not readable!');

        $this->class->set_private_key_by_file('invalid_file');
    }

    /**
     * Test that set_private_key_by_file() returns exception when the file failed to read.
     *
     * @covers Lunr\Vortex\FCM\FCMDispatcher::set_private_key_by_file
     */
    public function testSetPrivateKeyByFileReturnExceptionIfFileFailedToRead(): void
    {
        $this->mock_function('file_get_contents', fn() => FALSE);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('File does not exists or is not readable!');

        $this->class->set_private_key_by_file('private.key');

        $this->unmock_function('file_get_contents');
    }

    /**
     * Test that set_private_key_by_file() sets the private_key.
     *
     * @covers Lunr\Vortex\FCM\FCMDispatcher::set_private_key_by_file
     */
    public function testSetPrivateKeyByFileSetsPrivateKey(): void
    {
        $this->mock_function('file_get_contents', fn() => 'key');

        $this->class->set_private_key_by_file('private.key');

        $this->assertPropertyEquals('private_key', 'key');

        $this->unmock_function('file_get_contents');
    }

    /**
     * Test the return of set_private_key_by_file().
     *
     * @covers Lunr\Vortex\FCM\FCMDispatcher::set_private_key_by_file
     */
    public function testSetPrivateKeyByFileReturnsSelfReference(): void
    {
        $this->mock_function('file_get_contents', fn() => 'key');

        $this->assertEquals($this->class, $this->class->set_private_key_by_file('private.key'));

        $this->unmock_function('file_get_contents');
    }

    /**
     * Test that set_oauth_token() sets the oauth_token.
     *
     * @covers Lunr\Vortex\FCM\FCMDispatcher::set_oauth_token
     */
    public function testSetOAuthTokenSetsOAuthToken(): void
    {
        $this->class->set_oauth_token('token');

        $this->assertPropertyEquals('oauth_token', 'token');
    }

}

?>
