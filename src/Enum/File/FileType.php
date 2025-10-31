<?php

declare(strict_types=1);

namespace App\Enum\File;

enum FileType
{
    case IMAGE;

    public function getMimeTypes(): array
    {
        return match($this) 
        {
            FileType::IMAGE => [
                'image/jpeg',
                'image/png'
            ]
        };
    }
}