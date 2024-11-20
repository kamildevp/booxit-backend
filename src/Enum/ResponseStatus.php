<?php

namespace App\Enum;

enum ResponseStatus: string
{
    case SUCCESS = 'success';
    case FAIL = 'fail';
    case ERROR = 'error';
}