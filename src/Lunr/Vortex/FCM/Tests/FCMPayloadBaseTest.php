<?php

/**
 * This file contains the FCMPayloadBaseTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2017 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Vortex\FCM\Tests;

/**
 * This class contains the Base tests of the FCMPayload class.
 *
 * @covers Lunr\Vortex\FCM\FCMPayload
 */
class FCMPayloadBaseTest extends FCMPayloadTest
{

    /**
     * Test elements is initialized with high priority.
     */
    public function testElementsIsInitializedWithHighPriority(): void
    {
        $elements = [
            'android' => [ 'priority' => 'HIGH' ]
        ];

        $this->assertPropertySame('elements', $elements);
    }

}

?>
