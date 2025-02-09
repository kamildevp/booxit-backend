<?php

namespace App\Enum\User;


enum UserSetterGroup: string
{
    case ALL = 'Default';
    case PATCH = 'user-patch';
}