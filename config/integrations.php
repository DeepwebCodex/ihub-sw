<?php

return [

    'casino' => [
        'secret_word'   => 'ererr4SD',
        'service_id'    => 28,
        'error_codes' => [
            'signature' => 11, //Invalid signature
            'time'      => 10, //Time Expired
            'token'     => 6,   //Invalid token format
            'user_not_found' => 4
        ]
    ]

];