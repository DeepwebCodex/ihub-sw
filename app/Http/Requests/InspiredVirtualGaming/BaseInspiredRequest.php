<?php

namespace App\Http\Requests\InspiredVirtualGaming;

use App\Components\Traits\MetaDataTrait;
use App\Exceptions\Api\ApiHttpException;
use App\Http\Requests\ApiRequest;
use App\Http\Requests\ApiValidationInterface;
use Illuminate\Http\Request;

/**
 * Class AuthRequest
 * @package App\Http\Requests\EuroGamesTech
 */
class BaseInspiredRequest extends ApiRequest implements ApiValidationInterface
{
    use MetaDataTrait;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @param Request $request
     * @return bool
     */
    public function authorize(Request $request)
    {
        if(config('integrations.inspired.block_ips', true) && !in_array(get_client_ip(), config('integrations.inspired.allowed_ips', [])))
        {
            return false;
        }

        return true;
    }

    public function failedAuthorization()
    {
        throw new ApiHttpException('401', 'BADFORMAT');
    }

    public function response(array $errors)
    {
        $firstError = $this->getFirstError($errors);

        throw new ApiHttpException('400',
            array_get($firstError, 'message', 'Invalid input')
        );
    }

    function rules()
    {
        return [];
    }
}
