<?php

/**
 * This file contains priority types for delivering Firebase Cloud Messaging Notifications.
 *
 * SPDX-FileCopyrightText: Copyright 2018 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Vortex\FCM;

/**
 * Firebase Cloud Messaging Notification Priority Types for Android.
 */
enum FCMAndroidPriority: string
{

    /**
     * Deliver notification immediately.
     */
    case High = 'HIGH';

    /**
     * Deliver notification with normal priority.
     */
    case Normal = 'NORMAL';

}

?>
