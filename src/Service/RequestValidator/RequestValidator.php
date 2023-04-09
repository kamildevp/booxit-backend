<?php

namespace App\Service\RequestValidator;

use App\Exceptions\InvalidRequestException;

class RequestValidator{
    
    public function validateRequest(array $requestParameters, $requiredParameters){
        
        foreach($requiredParameters as $parameterName){
            if(!array_key_exists($parameterName, $requestParameters)){
                throw new InvalidRequestException("Parameter {$parameterName} is required");
            }
        }

        $diff = array_diff(array_keys($requestParameters), $requiredParameters);
        if(count($diff) > 0){
            $parameterName = array_values($diff)[0];
            throw new InvalidRequestException("Parameter {$parameterName} is not allowed");
        }
    }
}