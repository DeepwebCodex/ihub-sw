<?php

namespace App\Http\Requests\Validation;

use App\Exceptions\Api\ApiHttpException;
use Illuminate\Support\Facades\Request;
use App\Components\Integrations\GameArt\CodeMapping;

class GameArtValidation
{

    public function validateKey($attribute, $value, $parameters, $validator)
    {
        if (!($request = Request::getFacadeRoot())) {
            return false;
        }

        $query = $request->all();
        $key = $query['key'];
        unset($query['key']);
        $currency = json_decode($query['remote_data'], true)['currency'];

        if($key != hash('sha1', config('integrations.gameart')[$currency] . http_build_query($query))) {
            throw new ApiHttpException(500, "Invalid key", CodeMapping::getByMeaning(CodeMapping::INVALID_KEY));
        }

        return true;
    }

}