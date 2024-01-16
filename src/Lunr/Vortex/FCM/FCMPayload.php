<?php

/**
 * This file contains functionality to generate Firebase Cloud Messaging Push Notification payloads.
 *
 * SPDX-FileCopyrightText: Copyright 2017 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Vortex\FCM;

use Lunr\Vortex\APNS\APNSPriority;
use ReflectionClass;

/**
 * Firebase Cloud Messaging Push Notification Payload Generator.
 */
class FCMPayload
{

    /**
     * Array of Push Notification elements.
     * @var array
     */
    protected array $elements;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->elements = [];

        $this->elements['android']['priority'] = FCMAndroidPriority::High->value;
    }

    /**
     * Destructor.
     */
    public function __destruct()
    {
        unset($this->elements);
    }

    /**
     * Construct the payload for the push notification.
     *
     * @param int $flag The flag to encode the payload with
     *
     * @return string|false FCMPayload
     */
    public function get_json_payload(int $flag = 0): string|false
    {
        return json_encode([ 'message' => $this->elements ], $flag);
    }

    /**
     * Sets the payload key collapse_key.
     *
     * An arbitrary string that is used to collapse a group of alike messages
     * when the device is offline, so that only the last message gets sent to the client.
     *
     * @param string $key The notification collapse key identifier
     *
     * @return FCMPayload Self Reference
     */
    public function set_collapse_key(string $key): self
    {
        $this->elements['android']['collapse_key']             = $key;
        $this->elements['apns']['headers']['apns-collapse-id'] = $key;

        return $this;
    }

    /**
     * Sets the payload key data.
     *
     * The fields of data represent the key-value pairs of the message's payload data.
     *
     * @param array $data The actual notification information
     *
     * @return FCMPayload Self Reference
     */
    public function set_data(array $data): self
    {
        $this->elements['data'] = $data;

        return $this;
    }

    /**
     * Sets the payload key ttl for android devices.
     *
     * It defines how long (in seconds) the message should be kept on the Android storage,
     * if the device is offline.
     *
     * @param int $ttl The time in seconds for the notification to stay alive
     *
     * @return FCMPayload Self Reference
     */
    public function set_time_to_live(int $ttl): self
    {
        $this->elements['android']['ttl'] = (string) $ttl . 's';

        return $this;
    }

    /**
     * Check whether a condition is set
     *
     * @return bool TRUE if condition is present.
     */
    public function has_condition(): bool
    {
        return isset($this->elements['condition']);
    }

    /**
     * Check whether a condition is set
     *
     * @return bool TRUE if condition is present.
     */
    public function has_topic(): bool
    {
        return isset($this->elements['topic']);
    }

    /**
     * Sets the payload key notification.
     *
     * The fields of data represent the key-value pairs of the message's payload notification data.
     *
     * @param array $notification The actual notification information
     *
     * @return FCMPayload Self Reference
     */
    public function set_notification(array $notification): self
    {
        $this->elements['notification'] = $notification;

        return $this;
    }

    /**
     * Sets the notification as providing content.
     *
     * @param bool $val Value for the "content_available" field.
     *
     * @return FCMPayload Self Reference
     */
    public function set_content_available(bool $val): self
    {
        $this->elements['apns']['payload']['aps']['content-available'] = (int) $val;

        return $this;
    }

    /**
     * Sets the topic name to send the message to.
     *
     * @param string $topic String of the topic name
     *
     * @return FCMPayload Self Reference
     */
    public function set_topic(string $topic): self
    {
        $this->elements['topic'] = $topic;

        return $this;
    }

    /**
     * Sets the condition to send the message to. For example:
     * 'TopicA' in topics && ('TopicB' in topics || 'TopicC' in topics)
     *
     * You can include up to five topics in your conditional expression.
     * Conditions support the following operators: &&, ||, !
     *
     * @param string $condition Key-value pairs of payload data
     *
     * @return FCMPayload Self Reference
     */
    public function set_condition(string $condition): self
    {
        $this->elements['condition'] = $condition;

        return $this;
    }

    /**
     * Mark the notification as mutable.
     *
     * @param bool $mutable Notification mutable_content value.
     *
     * @return FCMPayload Self Reference
     */
    public function set_mutable_content(bool $mutable): self
    {
        $this->elements['apns']['payload']['aps']['mutable-content'] = (int) $mutable;

        return $this;
    }

    /**
     * Mark the notification priority.
     *
     * @param string $priority Notification priority value.
     *
     * @return FCMPayload Self Reference
     */
    public function set_priority(string $priority): self
    {
        $priority = strtoupper($priority);

        if (FCMAndroidPriority::tryFrom($priority) !== NULL)
        {
            $this->elements['android']['priority'] = $priority;
        }

        $priority_class = new ReflectionClass(APNSPriority::class);
        $priorities     = $priority_class->getConstants();
        if (in_array($priority, array_keys($priorities)))
        {
            $this->elements['apns']['headers']['apns-priority'] = $priorities[$priority];
        }

        return $this;
    }

    /**
     * Set additional FCM values in the 'fcm_options' key.
     *
     * @param string $key   Options key.
     * @param string $value Options value.
     *
     * @return FCMPayload Self Reference
     */
    public function set_options(string $key, string $value): self
    {
        $this->elements['fcm_options'][$key] = $value;

        return $this;
    }

    /**
     * Set the token of the target for the notification.
     *
     * @param string $token Token of the target for the notification.
     *
     * @return FCMPayload Self Reference
     */
    public function set_token(string $token): self
    {
        $this->elements['token'] = $token;

        return $this;
    }

    /**
     * Sets the payload category.
     *
     * @param string $category The category to set it to
     *
     * @return FCMPayload Self Reference
     */
    public function set_category(string $category): self
    {
        $this->elements['android']['notification']['click_action'] = $category;
        $this->elements['apns']['payload']['aps']['category']      = $category;

        return $this;
    }

    /**
     * Sets the tag of the notification for android notifications.
     *
     * @param string $tag The tag to set it to
     *
     * @return FCMPayload Self Reference
     */
    public function set_tag(string $tag): self
    {
        $this->elements['android']['notification']['tag'] = $tag;

        return $this;
    }

    /**
     * Sets the color of the notification for android notifications.
     *
     * @param string $color The color to set it to
     *
     * @return FCMPayload Self Reference
     */
    public function set_color(string $color): self
    {
        $this->elements['android']['notification']['color'] = $color;

        return $this;
    }

    /**
     * Sets the icon of the notification for android notifications.
     *
     * @param string $icon The icon to set it to
     *
     * @return FCMPayload Self Reference
     */
    public function set_icon(string $icon): self
    {
        $this->elements['android']['notification']['icon'] = $icon;

        return $this;
    }

    /**
     * Sets the notification sound.
     *
     * @param string $sound The sound to set it to
     *
     * @return FCMPayload Self Reference
     */
    public function set_sound(string $sound): self
    {
        $this->elements['android']['notification']['sound'] = $sound;
        $this->elements['apns']['payload']['aps']['sound']  = $sound;

        return $this;
    }

}

?>
