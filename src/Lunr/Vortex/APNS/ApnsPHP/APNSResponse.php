<?php

/**
 * This file contains an abstraction for the response from the APNS server.
 *
 * SPDX-FileCopyrightText: Copyright 2016 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Vortex\APNS\ApnsPHP;

use Lunr\Vortex\PushNotificationResponseInterface;
use Lunr\Vortex\PushNotificationStatus;
use Psr\Log\LoggerInterface;

/**
 * Apple Push Notification Service response wrapper.
 */
class APNSResponse implements PushNotificationResponseInterface
{
    use APNSPushNotificationStatusTrait;

    /**
     * Shared instance of a Logger class.
     *
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * The statuses per endpoint.
     * @var array<PushNotificationStatus::*>
     */
    protected array $statuses;

    /**
     * Raw payload that was sent to APNS.
     * @var string
     */
    protected string $payload;

    /**
     * Constructor.
     *
     * @param LoggerInterface $logger            Shared instance of a Logger.
     * @param array           $endpoints         The endpoints the message was sent to
     * @param array           $invalid_endpoints List of invalid endpoints detected before the push.
     * @param array|null      $errors            The errors response from the APNS Push.
     * @param string          $payload           Raw payload that was sent to APNS.
     */
    public function __construct(LoggerInterface $logger, array $endpoints, array $invalid_endpoints, ?array $errors, string $payload)
    {
        $this->logger   = $logger;
        $this->statuses = [];
        $this->payload  = $payload;

        $this->report_invalid_endpoints($invalid_endpoints);

        if (!is_null($errors))
        {
            $this->set_statuses($endpoints, $errors);
        }
        else
        {
            $this->report_error($endpoints);
        }
    }

    /**
     * Destructor.
     */
    public function __destruct()
    {
        unset($this->logger);
        unset($this->statuses);
        unset($this->payload);
    }

    /**
     * Define the status result for each endpoint.
     *
     * @param array $endpoints The endpoints the message was sent to
     * @param array $errors    The errors response from the APNS Push.
     *
     * @return void
     */
    protected function set_statuses(array $endpoints, array $errors): void
    {
        foreach ($errors as $error)
        {
            $message = $error['MESSAGE'];

            foreach ($error['ERRORS'] as $sub_error)
            {
                $status_code    = $sub_error['statusCode'];
                $status_message = $sub_error['statusMessage'];
                $reason         = NULL;
                $message_data   = json_decode($status_message, TRUE);
                if (json_last_error() === JSON_ERROR_NONE)
                {
                    $reason = $message_data['reason'] ?? NULL;
                }

                $status = $this->get_apns_error_status($status_code, $reason);

                $endpoint = $message->getRecipient();

                $this->statuses[$endpoint] = $status;

                $context = [ 'endpoint' => $endpoint, 'error' => $reason ?? $status_message ];
                $this->logger->warning('Dispatching APNS notification failed for endpoint {endpoint}: {error}', $context);
            }
        }

        foreach ($endpoints as $endpoint)
        {
            if (!isset($this->statuses[$endpoint]))
            {
                $this->statuses[$endpoint] = PushNotificationStatus::SUCCESS;
            }
        }
    }

    /**
     * Report invalid endpoints.
     *
     * @param array $invalid_endpoints The invalid endpoints
     *
     * @return void
     */
    protected function report_invalid_endpoints(array &$invalid_endpoints): void
    {
        foreach ($invalid_endpoints as $invalid_endpoint)
        {
            $this->statuses[$invalid_endpoint] = PushNotificationStatus::INVALID_ENDPOINT;
        }
    }

    /**
     * Report an error with the push notification.
     *
     * @param array $endpoints The endpoints the message was sent to
     *
     * @return void
     */
    protected function report_error(array &$endpoints): void
    {
        foreach ($endpoints as $endpoint)
        {
            if (!isset($this->statuses[$endpoint]))
            {
                $this->statuses[$endpoint] = PushNotificationStatus::ERROR;
            }
        }
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
        return isset($this->statuses[$endpoint]) ? $this->statuses[$endpoint] : PushNotificationStatus::UNKNOWN;
    }

}

?>
