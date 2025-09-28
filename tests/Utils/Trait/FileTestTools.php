<?php

declare(strict_types=1);

namespace App\Tests\Utils\Trait;

use App\Enum\File\UploadType;

trait FileTestTools
{
    protected function createUploadStorageDir(UploadType $uploadType) {
        $storagePath = $this->storageDir . $uploadType->getStoragePath();
        if(!$this->fs->exists($storagePath)){
            $this->fs->mkdir($storagePath, 0700);
        }
        
    }

    protected function clearStorage()
    {
        $this->fs->remove($this->storageDir);
    }
}