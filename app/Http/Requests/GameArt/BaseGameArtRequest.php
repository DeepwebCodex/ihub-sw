<?php

namespace App\Http\Requests\GameArt;

use App\Components\Integrations\GameArt\CodeMapping;
use App\Http\Requests\ApiRequest;
use App\Http\Requests\ApiValidationInterface;
use Illuminate\Http\Request;

class BaseGameArtRequest extends ApiRequest implements ApiValidationInterface
{
    protected $codeMapClass = CodeMapping::class;

    public function authorize(Request $request)
    {
        return true;
    }

    public function failedAuthorization()
    {
    }

    public function rules()
    {
        return [];
    }

}