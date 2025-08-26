<?php

declare(strict_types=1);

namespace App\Service\Entity;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\File;
use App\Entity\User;
use App\Enum\File\UploadType;
use Stof\DoctrineExtensionsBundle\Uploadable\UploadableManager;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;
use Symfony\Component\Mime\MimeTypes;

class FileService
{
    public function __construct(
        protected EntityManagerInterface $entityManager, 
        protected UploadableManager $uploadableManager,
        protected Security $security,
        protected string $storageDir,
    )
    {

    }

    public function uploadRawFile(string $content, ?string $contentType, UploadType $uploadType, ?File $overwriteFile = null, ?User $uploadedBy = null): File
    {
        if(empty($content)){
            throw new BadRequestException('No file uploaded');
        }
        $uploadedBy = $uploadedBy ?? $this->security->getUser();

        $extension = MimeTypes::getDefault()->getExtensions($contentType)[0] ?? 'bin';
        $fileName = sprintf('%s_%s.%s', $uploadType->value, uniqid(), $extension);
        $tmpPath = tempnam(sys_get_temp_dir(), 'upload_');
        file_put_contents($tmpPath, $content);

        try{
            $uploadedFile = new UploadedFile(
                $tmpPath,
                $fileName,
                $contentType,
                null,
                true
            ); 

            return $this->uploadFile($uploadedFile, $uploadType, $overwriteFile, $uploadedBy);
        }
        finally {
            if (file_exists($tmpPath)) {
                unlink($tmpPath);
            }
        }
    }

    public function uploadFile(UploadedFile $uploadedFile, UploadType $uploadType, ?File $overwriteFile = null, ?User $uploadedBy = null): File
    {
        if ($uploadedFile->getSize() > $uploadType->getMaxSize()) {
            throw new BadRequestException("File exceeds maximum size of " . $uploadType->getMaxSizeInMB() . ' MB');
        }

        $supportedMimeTypes = $uploadType->getFileType()->getMimeTypes();
        if(!in_array($uploadedFile->getMimeType(), $supportedMimeTypes)){
            throw new UnsupportedMediaTypeHttpException('Unsupported content type, supported types: ' . implode(',', $supportedMimeTypes));
        }

        $file = $overwriteFile ?? new File();
        $file->setType($uploadType->value);
        $file->setUploadedBy($uploadedBy);
        $this->entityManager->persist($file);
        $listener = $this->uploadableManager->getUploadableListener();
        $listener->setDefaultPath($this->storageDir . $uploadType->getStoragePath());
        $this->uploadableManager->markEntityToUpload($file, $uploadedFile);
        $this->entityManager->flush();

        return $file;
    }
}