<?php

namespace App\Exceptions\Api\Traits;


use App\Components\Formatters\BaseApiFormatter;
use App\Http\Controllers\Api\BaseApiController;
use Exception;
use Illuminate\Http\Request;

trait ApiHandlerTrait
{

    protected $controller;
    protected $method;
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
            list($this->controller, $this->method) = explode('@', $currentAction);

            if (is_subclass_of($this->controller, BaseApiController::class)) {
                return $this->controller;
            }
        }

        return false;
    }

    /**
     * @return string
     */
    protected function getNodeName(){
        return substr(strrchr($this->controller, "\\"), 1);
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