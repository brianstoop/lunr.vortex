<?php

/**
 * This file contains an abstraction for the response from the FCM server.
 *
 * SPDX-FileCopyrightText: Copyright 2017 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Vortex\FCM;

use Lunr\Vortex\PushNotificationResponseInterface;
use Lunr\Vortex\PushNotificationStatus;
use Psr\Log\LoggerInterface;
use WpOrg\Requests\Response;

/**
 * Firebase Cloud Messaging Push Notification response wrapper.
 */
class FCMResponse implements PushNotificationResponseInterface
{

    /**
     * Shared instance of a Logger class.
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * The response HTTP code.
     * @var int
     */
    private int $http_code;

    /**
     * The response body content.
     * @var string
     */
    private string $content;

    /**
     * Delivery status.
     * @var PushNotificationStatus::*
     */
    private int $status;

    /**
     * Push notification endpoint.
     * @var string
     */
    private string $endpoint;

    /**
     * Raw payload that was sent to FCM.
     * @var string
     */
    protected string $payload;

    /**
     * Constructor.
     *
     * @param Response        $response Requests\Response object.
     * @param LoggerInterface $logger   Shared instance of a Logger.
     * @param string          $endpoint The endpoint the message was sent to.
     * @param string          $payload  Raw payload that was sent to FCM.
     */
    public function __construct(Response $response, LoggerInterface $logger, string $endpoint, string $payload)
    {
        $this->logger   = $logger;
        $this->payload  = $payload;
        $this->endpoint = $endpoint;

        $this->http_code = $response->status_code;
        $this->content   = $response->body;

        if ($this->http_code == 200)
        {
            $this->set_status($endpoint);
        }
        else
        {
            $this->report_error($endpoint);
        }
    }

    /**
     * Destructor.
     */
    public function __destruct()
    {
        unset($this->logger);
        unset($this->http_code);
        unset($this->content);
        unset($this->status);
        unset($this->payload);
    }

    /**
     * Define the status result for each endpoint.
     *
     * @param string $endpoint The endpoints the message was sent to.
     *
     * @return void
     */
    private function set_status(string $endpoint): void
    {
        $json_content = json_decode($this->content, TRUE);

        if (!isset($json_content['results']))
        {
            $this->report_error($endpoint);
            return;
        }

        $result = $json_content['results'][0];

        if (!isset($result['error']))
        {
            $this->status = PushNotificationStatus::SUCCESS;
        }
        else
        {
            $this->report_endpoint_error($endpoint, $result['error']);
        }

        // We are supposed here to parse the new registration ids
    }

    /**
     * Get notification delivery status for an endpoint.
     *
     * @param string $endpoint Endpoint
     *
     * @return PushNotificationStatus::* Delivery status for the endpoint
     */
    public function get_status(string $endpoint): int
    {
        if ($endpoint != $this->endpoint)
        {
            return PushNotificationStatus::UNKNOWN;
        }

        return $this->status;
    }

    /**
     * Report an error with the push notification.
     *
     * @param string $endpoint The endpoints the message was sent to
     *
     * @return void
     */
    private function report_error(string $endpoint)
    {
        $error_message = 'Unknown error';
        $status        = PushNotificationStatus::UNKNOWN;

        if ($this->http_code == 400)
        {
            $error_message = "Invalid JSON ({$this->content})";
            $status        = PushNotificationStatus::ERROR;
        }
        elseif ($this->http_code == 401)
        {
            $error_message = 'Error with authentication';
            $status        = PushNotificationStatus::ERROR;
        }
        elseif ($this->http_code >= 500)
        {
            $error_message = 'Internal error';
            $status        = PushNotificationStatus::TEMPORARY_ERROR;
        }

        $this->status = $status;

        $context = [ 'endpoint' => $endpoint, 'error' => $error_message ];
        $this->logger->warning('Dispatching FCM notification failed for endpoint {endpoint}: {error}', $context);
    }

    /**
     * Report an error with the push notification for one endpoint.
     *
     * @param string $endpoint   Endpoint for which the push failed
     * @param string $error_code Error responde code
     *
     * @return void
     */
    private function report_endpoint_error(string $endpoint, string $error_code)
    {
        switch ($error_code)
        {
            case 'MissingRegistration':
                $status        = PushNotificationStatus::INVALID_ENDPOINT;
                $error_message = 'Missing registration token';
                break;
            case 'InvalidRegistration':
                $status        = PushNotificationStatus::INVALID_ENDPOINT;
                $error_message = 'Invalid registration token';
                break;
            case 'NotRegistered':
                $status        = PushNotificationStatus::INVALID_ENDPOINT;
                $error_message = 'Unregistered device';
                break;
            case 'InvalidPackageName':
                $status        = PushNotificationStatus::INVALID_ENDPOINT;
                $error_message = 'Invalid package name';
                break;
            case 'MismatchSenderId':
                $status        = PushNotificationStatus::INVALID_ENDPOINT;
                $error_message = 'Mismatched sender';
                break;
            case 'MessageTooBig':
                $status        = PushNotificationStatus::ERROR;
                $error_message = 'Message too big';
                break;
            case 'InvalidDataKey':
                $status        = PushNotificationStatus::ERROR;
                $error_message = 'Invalid data key';
                break;
            case 'InvalidTtl':
                $status        = PushNotificationStatus::ERROR;
                $error_message = 'Invalid time to live';
                break;
            case 'Unavailable':
                $status        = PushNotificationStatus::TEMPORARY_ERROR;
                $error_message = 'Timeout';
                break;
            case 'InternalServerError':
                $status        = PushNotificationStatus::TEMPORARY_ERROR;
                $error_message = 'Internal server error';
                break;
            case 'DeviceMessageRateExceeded':
                $status        = PushNotificationStatus::TEMPORARY_ERROR;
                $error_message = 'Device message rate exceeded';
                break;
            case 'TopicsMessageRateExceeded':
                $status        = PushNotificationStatus::TEMPORARY_ERROR;
                $error_message = 'Topics message rate exceeded';
                break;
            default:
                $status        = PushNotificationStatus::UNKNOWN;
                $error_message = $error_code;
                break;
        }

        $context = [ 'endpoint' => $endpoint, 'error' => $error_message ];
        $this->logger->warning('Dispatching FCM notification failed for endpoint {endpoint}: {error}', $context);

        $this->status = $status;
    }

}

?>
