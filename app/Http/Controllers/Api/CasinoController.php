<?php

namespace App\Http\Controllers\Api;

use App\Components\Formatters\JsonApiFormatter;
use App\Components\Formatters\XmlApiFormatter;
use App\Exceptions\Api\Templates\CasinoTemplate;
use App\Http\Controllers\Api\Base\BaseApiController;

use Illuminate\Http\Request;

class CasinoController extends BaseApiController
{

    public static $exceptionTemplate = CasinoTemplate::class;

    public function __construct(JsonApiFormatter $formatter)
    {
        parent::__construct($formatter);
    }

    public function index(Request $request){

       throw new \Exception(json_encode(['message' => 'very very bad', 'shit_got_real' => 15]), 5);

        //return response()->json(['name' => 'Abigail', 'state' => 'CA']);
    }
}
