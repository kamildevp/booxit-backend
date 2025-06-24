<?php

namespace App\Enum\User;

enum UserNormalizerGroup: string
{
    case PUBLIC = 'user-public';
    case PRIVATE = 'user-private';
}