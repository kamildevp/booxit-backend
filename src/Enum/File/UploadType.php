<?php

declare(strict_types=1);

namespace App\Enum\File;

enum UploadType: string
{
    case ORGANIZATION_BANNER = 'organization_banner';

    public function getFileType(): FileType
    {
        return match($this) 
        {
            UploadType::ORGANIZATION_BANNER => FileType::IMAGE
        };
    }

    public function getStoragePath(): string
    {
        return match($this) 
        {
            UploadType::ORGANIZATION_BANNER => 'organization/banner'
        };
    }

    public function getMaxSizeInMB(): int
    {
        return match($this) 
        {
            UploadType::ORGANIZATION_BANNER => 10
        };
    }

    public function getMaxSize(): int
    {
        $sizeInMB = $this->getMaxSizeInMB();
        return $sizeInMB * 1024 * 1024;
    }
}