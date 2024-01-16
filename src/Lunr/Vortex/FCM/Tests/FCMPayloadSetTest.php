<?php

/**
 * This file contains the FCMPayloadSetTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2018 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Vortex\FCM\Tests;

/**
 * This class contains tests for the setters of the FCMPayload class.
 *
 * @covers \Lunr\Vortex\FCM\FCMPayload
 */
class FCMPayloadSetTest extends FCMPayloadTest
{

    /**
     * Test set_collapse_key() works correctly.
     *
     * @covers Lunr\Vortex\FCM\FCMPayload::set_collapse_key
     */
    public function testSetCollapseKey(): void
    {
        $this->class->set_collapse_key('test');

        $value = $this->get_reflection_property_value('elements');

        $this->assertArrayHasKey('android', $value);
        $this->assertArrayHasKey('collapse_key', $value['android']);
        $this->assertEquals('test', $value['android']['collapse_key']);

        $this->assertArrayHasKey('apns', $value);
        $this->assertArrayHasKey('headers', $value['apns']);
        $this->assertArrayHasKey('apns-collapse-id', $value['apns']['headers']);
        $this->assertEquals('test', $value['apns']['headers']['apns-collapse-id']);
    }

    /**
     * Test fluid interface of set_collapse_key().
     *
     * @covers Lunr\Vortex\FCM\FCMPayload::set_collapse_key
     */
    public function testSetCollapseKeyReturnsSelfReference(): void
    {
        $this->assertSame($this->class, $this->class->set_collapse_key('collapse_key'));
    }

    /**
     * Test set_time_to_live() works correctly.
     *
     * @covers Lunr\Vortex\FCM\FCMPayload::set_time_to_live
     */
    public function testSetTimeToLive(): void
    {
        $this->class->set_time_to_live(5);

        $value = $this->get_reflection_property_value('elements');

        $this->assertArrayHasKey('android', $value);
        $this->assertArrayHasKey('ttl', $value['android']);
        $this->assertEquals('5s', $value['android']['ttl']);
    }

    /**
     * Test fluid interface of set_time_to_live().
     *
     * @covers Lunr\Vortex\FCM\FCMPayload::set_time_to_live
     */
    public function testSetTimeToLiveReturnsSelfReference(): void
    {
        $this->assertSame($this->class, $this->class->set_time_to_live(15));
    }

    /**
     * Test set_notification() works correctly.
     *
     * @covers \Lunr\Vortex\FCM\FCMPayload::set_notification
     */
    public function testSetNotification(): void
    {
        $this->class->set_notification([ 'key' => 'value' ]);

        $value = $this->get_reflection_property_value('elements');

        $this->assertArrayHasKey('notification', $value);
        $this->assertEquals([ 'key' => 'value' ], $value['notification']);
    }

    /**
     * Test fluid interface of set_notification().
     *
     * @covers \Lunr\Vortex\FCM\FCMPayload::set_notification
     */
    public function testSetNotificationReturnsSelfReference(): void
    {
        $this->assertSame($this->class, $this->class->set_notification([]));
    }

    /**
     * Test set_priority() works correctly.
     *
     * @covers \Lunr\Vortex\FCM\FCMPayload::set_priority
     */
    public function testSetPriority(): void
    {
        $this->class->set_priority('normal');

        $value = $this->get_reflection_property_value('elements');

        $this->assertArrayHasKey('android', $value);
        $this->assertArrayHasKey('priority', $value['android']);
        $this->assertEquals('NORMAL', $value['android']['priority']);

        $this->assertArrayHasKey('apns', $value);
        $this->assertArrayHasKey('headers', $value['apns']);
        $this->assertArrayHasKey('apns-priority', $value['apns']['headers']);
        $this->assertEquals(5, $value['apns']['headers']['apns-priority']);
    }

    /**
     * Test set_priority() works correctly with an invalid priority.
     *
     * @covers \Lunr\Vortex\FCM\FCMPayload::set_priority
     */
    public function testSetPriorityInvalid(): void
    {
        $this->class->set_priority('cow');

        $value = $this->get_reflection_property_value('elements');

        $this->assertArrayHasKey('android', $value);
        $this->assertArrayHasKey('priority', $value['android']);
        $this->assertEquals('HIGH', $value['android']['priority']);
    }

    /**
     * Test fluid interface of set_priority().
     *
     * @covers \Lunr\Vortex\FCM\FCMPayload::set_priority
     */
    public function testSetPriorityReturnsSelfReference(): void
    {
        $this->assertSame($this->class, $this->class->set_priority('high'));
    }

    /**
     * Test set_mutable_content() works correctly.
     *
     * @covers \Lunr\Vortex\FCM\FCMPayload::set_mutable_content
     */
    public function testSetMutableContent(): void
    {
        $this->class->set_mutable_content(TRUE);

        $value = $this->get_reflection_property_value('elements');

        $this->assertArrayHasKey('apns', $value);
        $this->assertArrayHasKey('payload', $value['apns']);
        $this->assertArrayHasKey('aps', $value['apns']['payload']);
        $this->assertArrayHasKey('mutable-content', $value['apns']['payload']['aps']);
        $this->assertEquals(1, $value['apns']['payload']['aps']['mutable-content']);
    }

    /**
     * Test fluid interface of set_mutable_content().
     *
     * @covers \Lunr\Vortex\FCM\FCMPayload::set_mutable_content
     */
    public function testSetMutableContentReturnsSelfReference(): void
    {
        $this->assertSame($this->class, $this->class->set_mutable_content(TRUE));
    }

    /**
     * Test set_data() works correctly.
     *
     * @covers \Lunr\Vortex\FCM\FCMPayload::set_data
     */
    public function testSetData(): void
    {
        $this->class->set_data([ 'key' => 'value' ]);

        $value = $this->get_reflection_property_value('elements');

        $this->assertArrayHasKey('data', $value);
        $this->assertEquals([ 'key' => 'value' ], $value['data']);
    }

    /**
     * Test fluid interface of set_data().
     *
     * @covers \Lunr\Vortex\FCM\FCMPayload::set_data
     */
    public function testSetDataReturnsSelfReference(): void
    {
        $this->assertSame($this->class, $this->class->set_data([]));
    }

    /**
     * Test set_topic() works correctly.
     *
     * @covers \Lunr\Vortex\FCM\FCMPayload::set_topic
     */
    public function testSetTopic(): void
    {
        $this->class->set_topic('News');

        $value = $this->get_reflection_property_value('elements');

        $this->assertArrayHasKey('topic', $value);
        $this->assertEquals('News', $value['topic']);
    }

    /**
     * Test fluid interface of set_topic().
     *
     * @covers \Lunr\Vortex\FCM\FCMPayload::set_topic
     */
    public function testSetTopicReturnsSelfReference(): void
    {
        $this->assertSame($this->class, $this->class->set_topic('data'));
    }

    /**
     * Test set_condition() works correctly.
     *
     * @covers \Lunr\Vortex\FCM\FCMPayload::set_condition
     */
    public function testSetCondition(): void
    {
        $this->class->set_condition("'TopicA' in topics && 'TopicB' in topics");

        $value = $this->get_reflection_property_value('elements');

        $this->assertArrayHasKey('condition', $value);
        $this->assertEquals("'TopicA' in topics && 'TopicB' in topics", $value['condition']);
    }

    /**
     * Test fluid interface of set_condition().
     *
     * @covers \Lunr\Vortex\FCM\FCMPayload::set_condition
     */
    public function testSetConditionReturnsSelfReference(): void
    {
        $this->assertSame($this->class, $this->class->set_condition('data'));
    }

    /**
     * Test set_content_available() works correctly.
     *
     * @covers \Lunr\Vortex\FCM\FCMPayload::set_content_available
     */
    public function testSetContentAvailable(): void
    {
        $this->class->set_content_available(TRUE);

        $value = $this->get_reflection_property_value('elements');

        $this->assertArrayHasKey('apns', $value);
        $this->assertArrayHasKey('payload', $value['apns']);
        $this->assertArrayHasKey('aps', $value['apns']['payload']);
        $this->assertArrayHasKey('content-available', $value['apns']['payload']['aps']);
        $this->assertEquals(1, $value['apns']['payload']['aps']['content-available']);
    }

    /**
     * Test fluid interface of set_content_available().
     *
     * @covers \Lunr\Vortex\FCM\FCMPayload::set_content_available
     */
    public function testSetContentAvailableReturnsSelfReference(): void
    {
        $this->assertSame($this->class, $this->class->set_content_available(TRUE));
    }

    /**
     * Test set_options() works correctly.
     *
     * @covers \Lunr\Vortex\FCM\FCMPayload::set_options
     */
    public function testSetOptions()
    {
        $this->class->set_options('analytics_label', 'fooBar');

        $value = $this->get_reflection_property_value('elements');

        $this->assertArrayHasKey('fcm_options', $value);
        $this->assertArrayHasKey('analytics_label', $value['fcm_options']);
        $this->assertEquals('fooBar', $value['fcm_options']['analytics_label']);
    }

    /**
     * Test fluid interface of set_content_available().
     *
     * @covers \Lunr\Vortex\FCM\FCMPayload::set_options
     */
    public function testSetOptionsReturnsSelfReference()
    {
        $this->assertSame($this->class, $this->class->set_options('analytics_label', 'fooBar'));
    }

    /**
     * Test set_token() works correctly.
     *
     * @covers \Lunr\Vortex\FCM\FCMPayload::set_token
     */
    public function testSetToken()
    {
        $this->class->set_token('endpoint_token');

        $value = $this->get_reflection_property_value('elements');

        $this->assertArrayHasKey('token', $value);
        $this->assertEquals('endpoint_token', $value['token']);
    }

    /**
     * Test fluid interface of set_token().
     *
     * @covers \Lunr\Vortex\FCM\FCMPayload::set_token
     */
    public function testSetTokenReturnsSelfReference()
    {
        $this->assertSame($this->class, $this->class->set_token('endpoint_token'));
    }

    /**
     * Test set_category() works correctly.
     *
     * @covers \Lunr\Vortex\FCM\FCMPayload::set_category
     */
    public function testSetCategory()
    {
        $this->class->set_category('category');

        $value = $this->get_reflection_property_value('elements');

        $this->assertArrayHasKey('android', $value);
        $this->assertArrayHasKey('notification', $value['android']);
        $this->assertArrayHasKey('click_action', $value['android']['notification']);
        $this->assertEquals('category', $value['android']['notification']['click_action']);

        $this->assertArrayHasKey('apns', $value);
        $this->assertArrayHasKey('payload', $value['apns']);
        $this->assertArrayHasKey('aps', $value['apns']['payload']);
        $this->assertArrayHasKey('category', $value['apns']['payload']['aps']);
        $this->assertEquals('category', $value['apns']['payload']['aps']['category']);
    }

    /**
     * Test fluid interface of set_category().
     *
     * @covers \Lunr\Vortex\FCM\FCMPayload::set_category
     */
    public function testSetCategoryReturnsSelfReference()
    {
        $this->assertSame($this->class, $this->class->set_category('category'));
    }

    /**
     * Test set_tag() works correctly.
     *
     * @covers \Lunr\Vortex\FCM\FCMPayload::set_tag
     */
    public function testSetTag()
    {
        $this->class->set_tag('tag');

        $value = $this->get_reflection_property_value('elements');

        $this->assertArrayHasKey('android', $value);
        $this->assertArrayHasKey('notification', $value['android']);
        $this->assertArrayHasKey('tag', $value['android']['notification']);
        $this->assertEquals('tag', $value['android']['notification']['tag']);
    }

    /**
     * Test fluid interface of set_tag().
     *
     * @covers \Lunr\Vortex\FCM\FCMPayload::set_tag
     */
    public function testSetTagReturnsSelfReference()
    {
        $this->assertSame($this->class, $this->class->set_tag('tag'));
    }

    /**
     * Test set_color() works correctly.
     *
     * @covers \Lunr\Vortex\FCM\FCMPayload::set_color
     */
    public function testSetColor()
    {
        $this->class->set_color('red');

        $value = $this->get_reflection_property_value('elements');

        $this->assertArrayHasKey('android', $value);
        $this->assertArrayHasKey('notification', $value['android']);
        $this->assertArrayHasKey('color', $value['android']['notification']);
        $this->assertEquals('red', $value['android']['notification']['color']);
    }

    /**
     * Test fluid interface of set_color().
     *
     * @covers \Lunr\Vortex\FCM\FCMPayload::set_color
     */
    public function testSetColorReturnsSelfReference()
    {
        $this->assertSame($this->class, $this->class->set_color('red'));
    }

    /**
     * Test set_icon() works correctly.
     *
     * @covers \Lunr\Vortex\FCM\FCMPayload::set_icon
     */
    public function testSetIcon()
    {
        $this->class->set_icon('icon');

        $value = $this->get_reflection_property_value('elements');

        $this->assertArrayHasKey('android', $value);
        $this->assertArrayHasKey('notification', $value['android']);
        $this->assertArrayHasKey('icon', $value['android']['notification']);
        $this->assertEquals('icon', $value['android']['notification']['icon']);
    }

    /**
     * Test fluid interface of set_icon().
     *
     * @covers \Lunr\Vortex\FCM\FCMPayload::set_icon
     */
    public function testSetIconReturnsSelfReference()
    {
        $this->assertSame($this->class, $this->class->set_icon('icon'));
    }

    /**
     * Test set_sound() works correctly.
     *
     * @covers \Lunr\Vortex\FCM\FCMPayload::set_sound
     */
    public function testSetSound()
    {
        $this->class->set_sound('sound');

        $value = $this->get_reflection_property_value('elements');

        $this->assertArrayHasKey('android', $value);
        $this->assertArrayHasKey('notification', $value['android']);
        $this->assertArrayHasKey('sound', $value['android']['notification']);
        $this->assertEquals('sound', $value['android']['notification']['sound']);

        $this->assertArrayHasKey('apns', $value);
        $this->assertArrayHasKey('payload', $value['apns']);
        $this->assertArrayHasKey('aps', $value['apns']['payload']);
        $this->assertArrayHasKey('sound', $value['apns']['payload']['aps']);
        $this->assertEquals('sound', $value['apns']['payload']['aps']['sound']);
    }

    /**
     * Test fluid interface of set_sound().
     *
     * @covers \Lunr\Vortex\FCM\FCMPayload::set_sound
     */
    public function testSetSoundReturnsSelfReference()
    {
        $this->assertSame($this->class, $this->class->set_sound('sound'));
    }

}

?>
