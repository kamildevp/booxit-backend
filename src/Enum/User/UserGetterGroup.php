<?php

namespace App\Enum\User;

enum UserGetterGroup: string
{
    case ALL = 'Default';
    case ME = 'me';
    case LOGIN = 'login';
    case USER = 'user';
    case USERS = 'users';
    case ORGANIZATION_MEMBERS = 'organization-members';
    case ORGANIZATION_ADMINS =  'organization-admins';
    case SCHEDULE_ASSIGNMENTS = 'schedule-assignments';
    case USER_ORGANIZATIONS = 'user-organizations';
    case PUBLIC = 'user-public';
}