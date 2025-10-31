<?php

declare(strict_types=1);

namespace App\Tests\Feature\Organization\DataProvider;

use App\Tests\Utils\DataProvider\BaseDataProvider;

class OrganizationPatchDataProvider extends BaseDataProvider 
{
    public static function validDataCases()
    {
        return [
            [
                [
                    'name' => 'New Test Organization',
                    'description' => 'new test',
                    'address' => [
                        'street' => 'New Test street',
                        'city' => 'New Test city',
                        'region' => 'New Test region',
                        'postal_code' => '30-123',
                        'country' => 'Italy',
                        'place_id' => 'NewPlaceId',
                        'formatted_address' => 'New Test address',
                        'latitude' => 46.1,
                        'longitude' => 25.2
                    ]
                ],
                [
                    'name' => 'New Test Organization',
                    'description' => 'new test',
                    'address' => [
                        'street' => 'New Test street',
                        'city' => 'New Test city',
                        'region' => 'New Test region',
                        'postal_code' => '30-123',
                        'country' => 'Italy',
                        'place_id' => 'NewPlaceId',
                        'formatted_address' => 'New Test address',
                        'latitude' => 46.1,
                        'longitude' => 25.2
                    ]
                ],
            ],
        ];
    }

    public static function validationDataCases()
    {
        return [
            [
                [
                    'name' => '',
                    'description' => '',
                    'address' => [
                        'street' => '',
                        'city' => '',
                        'region' => '',
                        'postal_code' => '',
                        'country' => '',
                        'place_id' => '',
                        'formatted_address' => '',
                        'latitude' => -100,
                        'longitude' => -200
                    ]
                ],
                [
                    'name' => [
                        'Parameter must be at least 6 characters long',
                    ],
                    'address' => [
                        'street' => [
                            'This value should not be blank.',
                        ],
                        'city' => [
                            'This value should not be blank.',
                        ],
                        'region' => [
                            'This value should not be blank.',
                        ],
                        'postal_code' => [
                            'This value should not be blank.',
                        ],
                        'country' => [
                            'This value should not be blank.',
                        ],
                        'place_id' => [
                            'This value should not be blank.',
                        ],
                        'formatted_address' => [
                            'This value should not be blank.',
                        ],
                        'latitude' => [
                            'This value should be between -90 and 90.',
                        ],
                        'longitude' => [
                            'This value should be between -180 and 180.',
                        ],
                    ]
                ]
            ],
            [
                [
                    'name' => str_repeat('a', 51),
                    'description' => str_repeat('a', 2001),
                    'address' => [
                        'street' => str_repeat('a', 256),
                        'city' => str_repeat('a', 101),
                        'region' => str_repeat('a', 101),
                        'postal_code' => 'a',
                        'country' => str_repeat('a', 101),
                        'place_id' => str_repeat('a', 256),
                        'formatted_address' => 'address',
                        'latitude' => 100,
                        'longitude' => 200
                    ]
                ],
                [
                    'name' => [
                        'Parameter cannot be longer than 50 characters',
                    ],
                    'description' => [
                        'Parameter cannot be longer than 2000 characters',
                    ],
                    'address' => [
                        'street' => [
                            'This value is too long. It should have 255 characters or less.',
                        ],
                        'city' => [
                            'This value is too long. It should have 100 characters or less.',
                        ],
                        'region' => [
                            'This value is too long. It should have 100 characters or less.',
                        ],
                        'postal_code' => [
                            'Invalid postal code format',
                        ],
                        'country' => [
                            'This value is too long. It should have 100 characters or less.',
                        ],
                        'place_id' => [
                            'This value is too long. It should have 255 characters or less.',
                        ],
                        'latitude' => [
                            'This value should be between -90 and 90.',
                        ],
                        'longitude' => [
                            'This value should be between -180 and 180.',
                        ],
                    ]
                ]
            ],
            [
                [
                    'name' => 'Test Organization 2',
                    'description' => '',
                    'address' => [
                        'street' => 'Test street',
                        'city' => 'Test city',
                        'region' => 'Test region',
                        'postal_code' => '30-126',
                        'country' => 'Poland',
                        'place_id' => 'myPlaceId',
                        'formatted_address' => 'Test address',
                        'latitude' => 50.1,
                        'longitude' => 19.2
                    ]
                ],
                [
                    'name' => [
                        'Organization with provided name already exists',
                    ],
                ]
            ],
        ];
    }
}