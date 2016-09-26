<?php

namespace App\Exceptions\Api\Traits;


use App\Components\Formatters\BaseApiFormatter;
use App\Http\Controllers\Api\BaseApiController;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

trait ApiHandlerTrait
{
    /**
     * @param Request $request
     * @return mixed bool|string
     */
    protected function isApiCall(Request $request){

        $route = $request->route();

        if (!$route)
        {
            return false;
        }

        $currentAction = $route->getActionName();

        if($currentAction) {
            list($controller, $method) = explode('@', $currentAction);

            if (is_subclass_of($controller, BaseApiController::class)) {
                return $controller;
            }
        }

        return false;
    }

    protected function getApiExceptionResponse(string $controller, Exception $exception){
        $ref = new \ReflectionClass($controller);

        $constructor = $ref->getConstructor();

        if ($constructor != null && $constructorParams = $constructor->getParameters()) {

            foreach ($constructorParams as $param) {
                $paramClass = $param->getClass()->name;

                if ($paramClass && is_subclass_of($paramClass, BaseApiFormatter::class)) {

                    /**
                     * @var  BaseApiFormatter $formatterInstance
                     */
                    $formatterInstance = new $paramClass();
                    $formatterInstance->setTemplate($controller::$exceptionTemplate);

                    return $formatterInstance->formatException($exception);
                }
            }
        }

        return null;
    }
}