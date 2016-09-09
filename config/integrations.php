<?php

return [

    'casino' => [
        'secret_word'   => 'ererr4SD',
        'service_id'    => 28,
        'error_codes' => [
            'signature' => 11,      //Invalid signature
            'time'      => 10,      //Time Expired
            'token'     => 6,       //Invalid token format
            'user_not_found' => 4,  //User not found
            'wallet' => 14,         //Invalid wallet
            'service'   => 13,      //Invalid Service,
            'currency'  => 3,       //Currency mismatch
            'money'     => 2        //Not enough money
        ]
    ]

];