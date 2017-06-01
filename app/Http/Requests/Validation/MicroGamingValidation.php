<?php

namespace App\Http\Requests\Validation;

use iHubGrid\SeamlessWalletCore\GameSession\TokenControl\TokenControl;
use App\Components\Integrations\MicroGaming\CodeMapping;
use iHubGrid\ErrorHandler\Exceptions\Api\ApiHttpException;

class MicroGamingValidation
{

    /**
     * @var TokenControl
     */
    protected $tokenControl;

    public function __construct()
    {
        $this->tokenControl = new TokenControl('micro_gaming', 7);
    }

    public function validateFirstUseToken($attribute, $value, $parameters, $validator)
    {
        if($this->tokenControl->isUsed($value)) {
            throw new ApiHttpException(400, null, CodeMapping::getByMeaning(CodeMapping::TIME_EXPIRED));
        }

        $this->tokenControl->setUsed($value);

        return true;
    }

    public function validateToken($attribute, $value, $parameters, $validator)
    {
        $message = null;

        if(!$value){
            throw new ApiHttpException(400, "Token field is empty", CodeMapping::getByMeaning(CodeMapping::INVALID_TOKEN));
        }

        if(!GlobalValidation::CheckSessionToken(null, $value, null, null))
        {
            throw new ApiHttpException(400, "Invalid session", CodeMapping::getByMeaning(CodeMapping::INVALID_TOKEN));
        }

        return true;
    }

    public function validatePlayType($attribute, $value, $parameters, $validator)
    {
        if(!in_array($value, ['bet', 'win', 'refund']))
        {
            throw new ApiHttpException(501, "Playtype '{$value}' is not implemented", CodeMapping::getByMeaning(CodeMapping::UNKNOWN_METHOD));
        }

        return true;
    }
}