<?php
/**
 * Created by PhpStorm.
 * User: doomsentinel
 * Date: 9/9/16
 * Time: 12:27 PM
 */

namespace App\Components\Users\Decorators;


use App\Components\Integrations\EuroGamesTech\CodeMapping;
use App\Components\Users\IntegrationUser;
use App\Exceptions\Api\ApiHttpException;

class UserEgt
{
    private $user;

    public function __construct(IntegrationUser $user) {
        $this->user = $user;
    }

    /**
     * @param string $inputCurrency
     */
    public function checkInputCurrency(string $inputCurrency){
        if($this->user->getCurrency() != $inputCurrency){
            throw new ApiHttpException(409, "Currency mismatch", CodeMapping::getByMeaning(CodeMapping::INVALID_CURRENCY));
        }
    }
}