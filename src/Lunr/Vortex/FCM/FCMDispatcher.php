<?php

/**
 * This file contains functionality to dispatch Firebase Cloud Messaging Push Notifications.
 *
 * SPDX-FileCopyrightText: Copyright 2017 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Vortex\FCM;

use InvalidArgumentException;
use Lunr\Vortex\PushNotificationDispatcherInterface;
use Psr\Log\LoggerInterface;
use WpOrg\Requests\Exception as RequestsException;
use WpOrg\Requests\Response;
use WpOrg\Requests\Session;

/**
 * Firebase Cloud Messaging Push Notification Dispatcher.
 */
class FCMDispatcher implements PushNotificationDispatcherInterface
{
    /**
     * Push Notification authentication token.
     * @var string
     */
    protected string $auth_token;

    /**
     * Shared instance of the Requests\Session class.
     * @var Session
     */
    protected Session $http;

    /**
     * Shared instance of a Logger class.
     *
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * Url to send the FCM push notification to.
     * @var string
     */
    private const GOOGLE_SEND_URL = 'https://fcm.googleapis.com/fcm/send';

    /**
     * Constructor.
     *
     * @param Session         $http   Shared instance of the Requests\Session class.
     * @param LoggerInterface $logger Shared instance of a Logger.
     */
    public function __construct(Session $http, LoggerInterface $logger)
    {
        $this->http       = $http;
        $this->logger     = $logger;
        $this->auth_token = '';
    }

    /**
     * Destructor.
     */
    public function __destruct()
    {
        unset($this->auth_token);
        unset($this->http);
        unset($this->logger);
    }

    /**
     * Getter for FCMResponse.
     *
     * @param Response        $http_response Requests\Response object.
     * @param LoggerInterface $logger        Shared instance of a Logger.
     * @param string          $endpoint      The endpoint the message was sent to.
     * @param string          $payload       Raw payload that was sent to FCM.
     *
     * @return FCMResponse
     */
    public function get_response(Response $http_response, LoggerInterface $logger, string $endpoint, string $payload): FCMResponse
    {
        return new FCMResponse($http_response, $logger, $endpoint, $payload);
    }

    /**
     * Push the notification.
     *
     * @param object   $payload   Payload object
     * @param string[] $endpoints Endpoints to send to in this batch
     *
     * @return FCMResponse Response object
     */
    public function push(object $payload, array &$endpoints): FCMResponse
    {
        if (!$payload instanceof FCMPayload)
        {
            throw new InvalidArgumentException('Invalid payload object!');
        }

        if ($endpoints === [])
        {
            throw new InvalidArgumentException('No endpoints provided!');
        }

        $headers = [
            'Content-Type'  => 'application/json',
            'Authorization' => 'key=' . $this->auth_token,
        ];

        $tmp_payload = json_decode($payload->get_payload(), TRUE);

        $tmp_payload['to'] = $endpoints[0];

        $json_payload = json_encode($tmp_payload, JSON_UNESCAPED_UNICODE);

        try
        {
            $options = [
                'timeout'         => 15, // timeout in seconds
                'connect_timeout' => 15 // timeout in seconds
            ];

            $http_response = $this->http->post(self::GOOGLE_SEND_URL, $headers, $json_payload, $options);
        }
        catch (RequestsException $e)
        {
            $this->logger->warning(
                'Dispatching FCM notification(s) failed: {message}',
                [ 'message' => $e->getMessage() ]
            );

            $http_response = $this->get_new_response_object_for_failed_request();

            if ($e->getType() == 'curlerror' && curl_errno($e->getData()) == 28)
            {
                $http_response->status_code = 500;
            }
        }

        return $this->get_response($http_response, $this->logger, $endpoints[0], $json_payload);
    }

    /**
     * Set the the auth token for the http headers.
     *
     * @param string $auth_token The auth token for the fcm push notifications
     *
     * @return FCMDispatcher Self reference
     */
    public function set_auth_token(string $auth_token): self
    {
        $this->auth_token = $auth_token;

        return $this;
    }

    /**
     * Get a Requests\Response object for a failed request.
     *
     * @return Response New instance of a Requests\Response object.
     */
    protected function get_new_response_object_for_failed_request(): Response
    {
        $http_response = new Response();

        $http_response->url = self::GOOGLE_SEND_URL;

        return $http_response;
    }

}

?>
