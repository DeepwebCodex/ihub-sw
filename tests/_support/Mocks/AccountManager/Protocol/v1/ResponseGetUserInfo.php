<?php

namespace Testing\AccountManager\Protocol\v1;

use Exception;
use Testing\AccountManager\Protocol\v1\DefaultParams;
use Testing\AccountManager\Protocol\v1\ResponseInterface;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ResponseUserInfo
 *
 * @author petroff
 */
class ResponseGetUserInfo implements ResponseInterface
{

    const SERVICE_IDS = [
        0, 1, 2, 3, 4, 6, 7, 8, 9, 10, 12, 13, 14, 16, 17, 20, 21, 22, 23,
        24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38, 39, 40,
        41, 42, 43, 44, 45, 46, 47, 48, 49, 50, 51, 53, 54, 107, 301
    ];

    public function getBase(array $params)
    {
        $default = DefaultParams::get();

        if (isset($params['user_id'])) {
            $user_id = $params['user_id'];
        }else{
            $user_id = $default->user_id;
        }

        if (isset($params['balance'])) {
            $balance = $params['balance'];
        }else{
            $balance = DefaultParams::AMOUNT_BALANCE;
        }

        if (isset($params['currency'])) {
            $currency = $params['currency'];
        }else{
            $currency = DefaultParams::CURRENCY;
        }

        if (isset($params['cashdesk'])) {
            $cashdesk = $params['cashdesk'];
        }else{
            $cashdesk = DefaultParams::CASHDESK_ID;
        }


        return [
            "__record" => "account",
            "cluster_id" => null,
            "id" => $user_id,
            "group" => 2,
            "login" => "ziwidif@rootfest.net",
            "email" => "ziwidif@rootfest.net",
            "sess" => "dp0i630iih0auinqsbmqtrfk27",
            "password" => "6ba5b1d8065b3560f0e29789213ff54e",
            "hash_type" => 1,
            "password_hach_old" => null,
            "last_ip" => "10.1.4.51",
            "status_id" => 2,
            "first_name" => "Тестер",
            "middle_name" => "Апоапопаоапо",
            "last_name" => "Паопаопаопао",
            "lang" => "ru",
            "timezone" => "Europe/Kiev",
            "tzoffset" => 7200.0,
            "phone_number" => "380501072339",
            "date_of_birth" => "1991-06-06 00:00:00",
            "country_id" => "UA",
            "city" => null,
            "zip" => null,
            "adress" => null,
            "question" => "Mother's maiden name?",
            "answer" => "filler",
            "registration_date" => "2016-10-21 07:35:48",
            "title" => "mr",
            "documents" => null,
            "cashdesk" => $cashdesk,
            "deleted" => 0,
            "wallets" => [
                [
                    "__record" => "wallet",
                    "user_id" => $user_id,
                    "payment_instrument_id" => 3,
                    "payment_instrument_name" => "Skrill",
                    "wallet_id" => "ziwidif@rootfest.net",
                    "wallet_account_id" => $currency,
                    "partner_id" => 1,
                    "currency" => $currency,
                    "is_default" => 0,
                    "is_active" => 1,
                    "deposit" => $balance,
                    "creation_date" => "2016-10-21 13:37:14",
                    "payment_instrument_transfer_time" => "00.00",
                    "cashdesk" => $cashdesk,
                    "deleted" => 0,
                ],
                [
                    "__record" => "wallet",
                    "user_id" => $user_id,
                    "payment_instrument_id" => 5,
                    "payment_instrument_name" => "Bonuses",
                    "wallet_id" => "3000053",
                    "wallet_account_id" => "BNS",
                    "partner_id" => 1,
                    "currency" => "BNS",
                    "is_default" => 0,
                    "is_active" => 0,
                    "deposit" => 0.0,
                    "creation_date" => "2016-10-21 07:35:48",
                    "payment_instrument_transfer_time" => "00.00",
                    "cashdesk" => $cashdesk,
                    "deleted" => 0,
                ],
            ],
            "user_services" => $this->getServices(),
            "trust_level" => 100,
            "blacklist" => 0,
            "loyalty_rating" => 0,
            "loyalty_points" => 0,
            "loyalty_months" => 0,
            "loyalty_deposit_count" => 0,
            "loyalty_rating_level" => 0,
            "fav_bet_club_user" => 1,
            "coupon" => null,
            "mobile_is_active" => 0,
            "email_is_active" => 1,
            "spam_ok" => 1,
            "partner_id" => 1,
            "data" => null,
            "token" => "89",
            "oib" => null,
            "nationality" => null,
            "region" => null,
            "fullname" => "Апаропао Апоапопаоапо Паопаопаопао",
        ];
    }

    public function getProtocol(array $params)
    {
        $params = $this->validation($params);
        $base = $this->getBase($params);
        return array_merge($base, $params);
    }

    public function validation(array $params): array
    {
        //nothing special
        return $params;
    }

    private function getServices()
    {
        return array_map(function($service_id) {
            return [
                "__record" => "user_service",
                "service_id" => $service_id,
                "is_enabled" => 1,
            ];
        }, self::SERVICE_IDS);
    }

}
