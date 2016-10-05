<?php

namespace App\Http\Controllers\Api;

use App\Components\Formatters\TextApiFormatter;
use App\Components\Traits\MetaDataTrait;
use App\Exceptions\Api\Templates\VirtualBoxingTemplate;
use App\Exceptions\Api\ApiHttpException;
use App\Models\VirtualBoxing\EventLinkModel;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Stringy\StaticStringy as S;

/**
 * Class CasinoController
 * @package App\Http\Controllers\Api
 */
class VirtualBoxingController extends BaseApiController
{
    use MetaDataTrait;

    public static $exceptionTemplate = VirtualBoxingTemplate::class;

    public function __construct(TextApiFormatter $formatter)
    {
        parent::__construct($formatter);

        $this->options = config('integrations.microGaming');

        $this->middleware('input.xml');
    }

    public function index(Request $request)
    {
        $method = $request->input('name', $request->input('type', 'error'));

        $method = (string) S::camelize($method);

        $this->addMetaField('method', $method);

        if(method_exists($this, $method)) {
            return app()->call([$this, $method], $request->all());
        }

        return app()->call([$this, 'error'], $request->all());
    }

    public function matchBet(Request $request)
    {
        $duplicate = EventLinkModel::getByVbId($request->input('match.scheduleId'));

        if($duplicate){
            return $this->respondOk(200, "Duplicate");
        }

        return $this->respondOk(200, '', [
            'event_id' => 5,
            'event_vb_id' => 10,
            't_id' => 25
        ]);
    }

    public function matchProgress(Request $request)
    {
        return $this->respondOk(200, '', [
            'event_id' => 5,
            'event_vb_id' => 10,
            't_id' => 25
        ]);
    }

    public function result(Request $request)
    {
        return $this->respondOk(200, '', [
            'event_id' => 5,
            'event_vb_id' => 10,
            't_id' => 25
        ]);
    }

    public function error()
    {
        throw new ApiHttpException(400, 'Miss element');
    }

    public function respondOk($statusCode = Response::HTTP_OK, string $message = "", array $payload = []){

        $payload = array_merge([
            'Done',
            $this->pullMetaField('method') ?: 'Error'
        ], [implode(':', $payload)]);

        return parent::respondOk($statusCode, $message, $payload);
    }
}
