<?php

declare(strict_types=1);

namespace App\Tests\Feature\Organization\DataProvider;

use App\Tests\Utils\DataProvider\BaseDataProvider;

class OrganizationUpdateBannerDataProvider extends BaseDataProvider 
{
    public static function validDataCases()
    {
        return [
            [
                'testImg1.jpg',
                'image/jpeg',
            ],
            [
                'testImg2.png',
                'image/png',
            ],
            [
                'testImg2.png',
                null,
            ],
        ];
    }

    public static function validationDataCases()
    {
        return [
            [
                '',
                'image/png',
                'No file uploaded',
                400,
            ],
            [
                'sample.pdf',
                'application/pdf',
                'Unsupported content type, supported types: image/jpeg,image/png',
                415,
            ],
            [
                'sample.pdf',
                'image/png',
                'Unsupported content type, supported types: image/jpeg,image/png',
                415,
            ],
        ];
    }
}