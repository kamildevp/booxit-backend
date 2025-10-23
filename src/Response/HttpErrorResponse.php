<?php

declare(strict_types=1);

namespace App\Response;

use App\Response\Interface\ExceptionResponseInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Throwable;

class HttpErrorResponse implements ExceptionResponseInterface
{
    public static function createFromException(Throwable $exception): ApiResponse
    {
        $previousException = $exception->getPrevious();

        if($previousException instanceof ValidationFailedException){
            $errors = [];
            $camelCaseConverter = new CamelCaseToSnakeCaseNameConverter();
            foreach ($previousException->getViolations() as $violation) {
                $propertyPath = $violation->getPropertyPath();
                $requestParameterName = $camelCaseConverter->normalize($propertyPath);
                $requestParameterName = str_replace(['[',']'], '.', $requestParameterName);
                $requestParameterName = str_replace('..', '.', $requestParameterName);
                $requestParameterName = trim($requestParameterName, '.');
                $errors[$requestParameterName][] = $violation->getMessage();
            }

            return new ValidationErrorResponse(array_undot($errors));
        }
        elseif($exception instanceof HttpException && $exception->getStatusCode() == 404){
            return new NotFoundResponse();
        }
        else{
            return new ServerErrorResponse();
        }
    }
} 