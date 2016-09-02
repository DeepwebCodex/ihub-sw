<?php

namespace App\Http\Controllers\Api;

use App\Components\Formatters\XmlApiFormatter;
use App\Components\Traits\MetaDataTrait;
use App\Exceptions\Api\Templates\CasinoTemplate;
use App\Http\Controllers\Api\Base\BaseApiController;
use Illuminate\Http\Request;

class CasinoController extends BaseApiController
{
    use MetaDataTrait;

    public static $exceptionTemplate = CasinoTemplate::class;

    public function __construct(XmlApiFormatter $formatter)
    {
        parent::__construct($formatter);

        $this->middleware('check.json')->except('gen_token');
    }

    public function index(Request $request){
        return $this->respondOk();
    }


}
