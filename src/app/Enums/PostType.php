<?php

namespace App\Enums;

enum PostType: string
{
    case CONDUCT_CODE = 'conduct_code';
    case CORPORATE_DOCUMENTATION = 'corporate_documentation';
    case USER_NOTICE = 'user_notice';
    case TERMS_CONDITIONS = 'terms_conditions';
}
