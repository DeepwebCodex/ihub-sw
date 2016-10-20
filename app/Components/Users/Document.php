<?php
/**
 * Created by PhpStorm.
 * User: doomsentinel
 * Date: 9/9/16
 * Time: 5:53 PM
 */

namespace App\Components\Users;

/**
 * @property integer $user_id
 * @property integer $payment_instrument_id
 * @property integer $document_type_id
 * @property string  $document_type_name
 * @property string  $api_name
 * @property string  $document_number
 * @property string  $document_place
 * @property string  $document_place_date
 * @property string  $document_expire_date
 * @property string  $document_image
 * @property string  $citizenship
 * @property string  $document_country
 * @property integer $document_cashdesk
 */
class Document
{
    private $attributes = [];

    public function __construct(array $data)
    {
        $this->attributes = $data;
    }

    public function __get($name)
    {
        return array_get($this->attributes, $name);
    }
}