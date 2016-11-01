<?php

namespace App\Exceptions\Api\VirtualBoxing;

use Symfony\Component\HttpFoundation\Response;

/**
 * Class DuplicateException
 * @package App\Exceptions\Api\VirtualBoxing
 */
class DuplicateException extends BaseException
{
    /**
     * DuplicateException constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->code = Response::HTTP_OK;
    }
}
