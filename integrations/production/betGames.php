<?php

return [
    'service_id' => 13,
    'secret' => 'BF15E-M8XW0-SPADR-VRC3X', // favbet.com
    'secret_separated' => [
        18 => [
            -8 => 'XQYRR-132HH-GX0WB-9V6E7', // favorit.com.ua
            -62 => 'MOQBD-8DGHZ-LRNR5-17REH', // mapp.favorit.com.ua
        ],
    ],
    'allowed_ips' => [],
    'routes'      => [
        'favbet'      => ['partner_id' => 1, 'cashdesk_id' => -5],
        'favbet-app'  => ['partner_id' => 1, 'cashdesk_id' => -5],
        'favorit'     => ['partner_id' => 18, 'cashdesk_id' => -8],
        'favorit-app' => ['partner_id' => 18, 'cashdesk_id' => -62],
    ],
];
