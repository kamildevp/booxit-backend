<?php

namespace App\Enum;

use App\Model\View;

enum ViewType
{
    case EMAIL_VERIFICATION_FAIL;
    case EMAIL_VERIFICATION_SUCCESS;

    public const TYPES = [
        'EMAIL_VERIFICATION_FAIL' => [
            'template' => 'emailVerification.html.twig',
            'params' => ['header' => 'Verification Failed', 'description' => '']
        ],
        'EMAIL_VERIFICATION_SUCCESS' => [
            'template' => 'emailVerification.html.twig',
            'params' => ['header' => 'Verification Completed', 'description' => 'Your email was verified successfully']
        ],
    ];

    public function getView(): View
    {
        $type = self::TYPES[$this->name];
        return new View($type['template'], $type['params']);
    }
}