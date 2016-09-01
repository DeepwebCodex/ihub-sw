<?php

namespace App\Http\Controllers\Api;

use App\Components\Formatters\JsonApiFormatter;
use App\Components\Formatters\XmlApiFormatter;
use App\Components\Traits\MetaDataTrait;
use App\Exceptions\Api\Templates\CasinoTemplate;
use App\Http\Controllers\Api\Base\BaseApiController;

class CasinoController extends BaseApiController
{
    use MetaDataTrait;

    public static $exceptionTemplate = CasinoTemplate::class;

    public function __construct(XmlApiFormatter $formatter)
    {
        parent::__construct($formatter);
    }

    public function index(){


        return $this->respondOk();
    }
}
