<?php

/**
 * This file contains APNSPushNotificationStatusTrait trait.
 *
 * SPDX-FileCopyrightText: Copyright 2023 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Vortex\APNS\ApnsPHP;

use Lunr\Vortex\PushNotificationStatus;

/**
 * Trait to determine the push notification status from APNS.
 */
trait APNSPushNotificationStatusTrait
{

    /**
     * Get notification delivery status for an endpoint.
     *
     * @param int         $status_code The http status of the response
     * @param string|null $reason      The reason of the error
     *
     * @return PushNotificationStatus::* Delivery status for the endpoint
     */
    public function get_apns_error_status(int $status_code, ?string $reason): int
    {
        switch ($status_code)
        {
            case APNSHttpStatus::ERROR_BAD_REQUEST:
            case APNSHttpStatus::ERROR_UNREGISTERED:
            case APNSBinaryStatus::ERROR_INVALID_TOKEN_SIZE:
            case APNSBinaryStatus::ERROR_INVALID_TOKEN:
                $status = PushNotificationStatus::INVALID_ENDPOINT;
                break;
            case APNSHttpStatus::TOO_MANY_REQUESTS:
            case APNSBinaryStatus::ERROR_PROCESSING:
                $status = PushNotificationStatus::TEMPORARY_ERROR;
                break;
            default:
                $status = PushNotificationStatus::UNKNOWN;
                break;
        }

        //Refine based on reasons in the HTTP status
        switch ($reason)
        {
            case APNSHttpStatusReason::ERROR_TOPIC_BLOCKED:
            case APNSHttpStatusReason::ERROR_CERTIFICATE_INVALID:
            case APNSHttpStatusReason::ERROR_CERTIFICATE_ENVIRONMENT:
            case APNSHttpStatusReason::ERROR_INVALID_AUTH_TOKEN:
                return PushNotificationStatus::ERROR;
            case APNSHttpStatusReason::ERROR_IDLE_TIMEOUT:
            case APNSHttpStatusReason::ERROR_EXPIRED_AUTH_TOKEN:
                return PushNotificationStatus::TEMPORARY_ERROR;
            case APNSHttpStatusReason::ERROR_BAD_TOKEN:
            case APNSHttpStatusReason::ERROR_NON_MATCHING_TOKEN:
                return PushNotificationStatus::INVALID_ENDPOINT;
            default:
                return $status;
        }
    }

}

?>
