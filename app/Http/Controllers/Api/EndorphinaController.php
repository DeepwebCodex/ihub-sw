<?php

namespace App\Http\Controllers\Api;

use App\Components\Integrations\NetEntertainment\CodeMapping;
use App\Components\Traits\MetaDataTrait;
use App\Exceptions\Api\ApiHttpException;
use App\Http\Requests\Validation\BetGamesValidation;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Request;
use function app;
use function config;

class EndorphinaController extends BaseApiController
{

    use MetaDataTrait;

    public static $exceptionTemplate = EndorphinaTemplate::class;

    public function __construct(EndorphinaApiFormatter $formatter)
    {
        parent::__construct($formatter);

        $this->options = config('integrations.endorphina');

        $this->middleware('check.ip:endorphina');
        $this->middleware('input.xml')->except(['error']);


        //Validator::extend('check_token', 'App\Http\Requests\Validation\BetGamesValidation@checkToken');

    }

    public function index(Request $request) {
        $method = $request->input('cmd');
        $this->userId = $request->input('userId');
        $this->setMetaData([
            'imprint' => $request->all()
        ]);

        if (method_exists($this, $method)) {
            return app()->call([$this, $method], $request->all());
        }
        return app()->call([$this, 'error'], $request->all());
    }

    public function error() {
        throw new ApiHttpException(404, 'Unknown method', CodeMapping::getByMeaning(CodeMapping::UNKNOWN_METHOD));
    }

}
